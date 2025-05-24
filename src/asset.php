<?php

namespace phuety;

class asset {

    public array $assets = [];
    public array $css = [];
    public bool $css_written = false;

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

    public function get($position = null) {
        return join("\n", array_map(function ($a) {
            // html tag
            return $a[3];
        }, array_filter($this->assets, function ($a) use ($position) {
            return $a[1] == $position;
        })));
    }

    public function write_css(string $cname, string $compile_dir, string $asset_dir, string $asset_url) {
        if ($this->css_written) return;
        $this->css_written = true;

        dbg("+++ write CSS", $this->css);
        $css = "";
        foreach ($this->css as $name => $fname) {
            dbg("load css", $compile_dir . "/$name");
            $css .= file_get_contents($compile_dir . "/$fname" . ".css");
        }
        file_put_contents($asset_dir . "/$cname" . ".css", $css);
        $this->push($name, [
            "css",
            "head",
            null,
            sprintf('<link rel="stylesheet" href="%s%s.css">', $asset_url, $cname)
        ]);
    }
}
