<?php

use phuety\component;
USESTATEMENTS

class NAME_component extends component {
    public string $uid = "UID";
    public bool $is_layout = ISLAYOUT;
    public string $name = "NAME";
    public bool $has_template = HAS_TEMPLATE;
    public bool $has_code = HAS_CODE;
    public bool $has_style = HAS_STYLE;
    public array $assets = ASSETS;

    function run_code($props){
        PHPCODE
        return get_defined_vars() + $props;
    }

    function render($__expr, $__data){?>
        RENDER
    <?}
}
