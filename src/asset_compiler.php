<?php

namespace phuety;

use Closure;

class asset_compiler {
    // TODO: inject setup: bun, prod/dev, etc.
    public function __construct(
        public string $compile_dir,
        public string $asset_dir,
        public string $asset_build_dir,
        public string $asset_url,
        public Closure $alias_resolver,
        public string $prefix = "",
        public ?string $use_bun = null,
    ) {
        $this->check_bun();
    }

    public function check_bun() {
        if ($this->use_bun !== null) return;
        $bun = $this->use_bun ?: "bun";
        $outp = shell_exec("$bun -v 2>&1");
        if (str_starts_with($outp, "1.")) {
            $this->use_bun = $bun;
        } else {
            $this->use_bun = "";
        }
    }

    public function compile_head_css(string $href): string {
        $info = pathinfo($href);
        $src_dir = str_replace("@assets", "", $info["dirname"]);
        // $src = $this->alias_resolver ????
        $src = $this->asset_dir . "/" . $src_dir . "/" . $info["basename"];
        $hash = hash_file('xxh3', $src);
        $dest = $this->asset_build_dir . "/" . $info["filename"] . "." . $hash . "." . $info["extension"];
        $new_name = "@assets/_build/" . $info["filename"] . "." . $hash . "." . $info["extension"];

        dbg("compile_head_css", $href, $info, $src, $dest);
        if (file_exists($dest)) return $new_name;

        if ($this->use_bun) {
            $css = shell_exec("$this->use_bun build --no-bundle $src");
            file_put_contents($dest, $css);
        } else {
            copy($src, $dest);
        }

        return $new_name;
    }

    static public function hash_file_rename(string $dirname, string $fname, string $extension): string {
        $hash = hash_file('xxh3', $dirname . "/$fname" . "." . $extension);
        $new_name = $fname . "." . $hash;
        rename($dirname . "/$fname" . "." . $extension, $dirname . "/$new_name" . "." . $extension);
        return $new_name;
    }
}
