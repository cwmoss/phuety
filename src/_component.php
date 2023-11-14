<?php

use phuety\component;
USESTATEMENTS

class NAME_component extends component {
    public string $uid = "UID";
    public bool $is_layout = ISLAYOUT;
    public string $name = "NAME";

    function run_code($props){
        PHPCODE
        return get_defined_vars();
    }
}
