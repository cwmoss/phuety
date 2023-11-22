<?php

use phuety\component;


class layout_component extends component {
    public string $uid = "layout-655e63bc21a71";
    public bool $is_layout = true;
    public string $name = "layout";

    function run_code($props){
        $bodyclass = ''; // $props['class'] ?? '';
$smile = "😃";

        return get_defined_vars() + $props;
    }
}
