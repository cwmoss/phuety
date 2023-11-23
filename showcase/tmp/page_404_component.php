<?php

use phuety\component;


class page_404_component extends component {
    public string $uid = "page_404-655f2fa2c7ebb";
    public bool $is_layout = true;
    public string $name = "page_404";

    function run_code($props){
        
        return get_defined_vars() + $props;
    }
}
