<?php

use phuety\component;


class page_about_component extends component {
    public string $uid = "page_about-655f33f3a193c";
    public bool $is_layout = false;
    public string $name = "page_about";

    function run_code($props){
        
        return get_defined_vars() + $props;
    }
}
