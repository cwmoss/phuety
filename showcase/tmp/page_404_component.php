<?php

use phuety\component;


class page_404_component extends component {
    public string $uid = "page_404-655e63a2c6e0b";
    public bool $is_layout = true;
    public string $name = "page_404";

    function run_code($props){
        
        return get_defined_vars() + $props;
    }
}
