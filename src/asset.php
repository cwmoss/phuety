<?php

namespace phuety;

class asset {

    public array $assets = [];
    public array $css = [];
    public bool $css_written = false;
    public bool $use_bun = false;

    // TODO: inject setup: bun, prod/dev, etc.
    public function __construct(public string $prefix = "") {
        $outp = shell_exec("bun -v 2>&1");
        $this->use_bun = str_starts_with($outp, "1.");
    }

    public function push($uid, $asset) {
        $id = match (true) {
            $asset[0] == 'script' && isset($asset[2]['src']) => $asset[2]['src'],
            $asset[0] == 'link' => $asset[2]['href'],
            default => $uid
        };
        if (!isset($this->assets[$id])) {
            $this->assets[$id] = $asset;
        }
    }

    public function push_css($tagname) {
        if (!isset($this->css[$tagname])) $this->css[$tagname] = str_replace(".", "_", $tagname);
    }

    static public function tag(array $tag_props, string $prefix) {
        $attrs = $tag_props[2];
        if (isset($attrs["href"]) || isset($attrs["src"])) {
            if (isset($attrs["href"])) {
                $attrs["href"] = $prefix . $attrs["href"];
            }
            if (isset($attrs["src"])) {
                $attrs["src"] = $prefix . $attrs["src"];
            }
            return tag::tag($tag_props[0], $attrs);
        }
        return $tag_props[3];
    }
    public function get($position = null) {
        return join("\n", array_map(function ($a) {
            // html tag
            return self::tag($a, $this->prefix);
        }, array_filter($this->assets, function ($a) use ($position) {
            return $a[1] == $position;
        })));
    }

    public function write_css(string $cname, string $compile_dir, string $asset_dir, string $asset_url) {
        if ($this->css_written) return;
        $this->css_written = true;

        // dbg("+++ write CSS", $this->css);
        $css = "";
        foreach ($this->css as $name => $fname) {
            // dbg("load css", $compile_dir . "/$name");
            $css .= file_get_contents($compile_dir . "/$fname" . ".css") . "\n";
        }
        if (!$css) return;
        file_put_contents($asset_dir . "/$cname" . ".css", $css);
        if ($this->use_bun) {
            $in = $asset_dir . "/$cname" . ".css";
            $syntax_down = shell_exec("bun build $in");
            file_put_contents($asset_dir . "/$cname" . ".css", $syntax_down);
        }
        $this->push($cname, [
            "css",
            "head",
            null,
            sprintf('<link rel="stylesheet" href="%s%s.css">', $asset_url, $cname)
        ]);
    }
}
