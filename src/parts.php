<?php

namespace phuety;

use Dom\Element;
use Dom\HTMLDocument;

class parts {

    public ?HTMLDocument $head = null;
    public ?HTMLDocument $dom = null;
    public string $render = "";
    public string $uid = "";
    public bool $is_layout = false;
    public string $compile_basedir = "";
    public string $src_file = "";

    public function __construct(
        public string $name = "",
        public string $php = "",
        public string $html = "",
        public ?int $php_start = null,
        public string $css = "",
        public array $js = [],
        public array $assets = [],
        // public $uid => $name . '---' . uniqid(),
        public int $total_rootelements = 0,
        public array $custom = []
    ) {
    }
}
