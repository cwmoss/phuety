<?php

use phuety\component;


class sc_logo_component extends component {
    public string $uid = "sc_logo-655f33f3a3097";
    public bool $is_layout = false;
    public string $name = "sc_logo";

    function run_code($props){
        
        return get_defined_vars() + $props;
    }
}
