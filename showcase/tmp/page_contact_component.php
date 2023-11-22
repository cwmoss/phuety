<?php

use phuety\component;


class page_contact_component extends component {
    public string $uid = "page_contact-655e63bb5e666";
    public bool $is_layout = false;
    public string $name = "page_contact";

    function run_code($props){
        
        return get_defined_vars() + $props;
    }
}
