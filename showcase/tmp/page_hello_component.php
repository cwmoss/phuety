<?php

use phuety\component;


class page_hello_component extends component {
    public string $uid = "page_hello-655e50e4c8ee7";
    public bool $is_layout = false;
    public string $name = "page_hello";

    function run_code($props){
        $title = "startseite!";
$name = "welt";
$ok = true;
$user = '1234';

        return get_defined_vars() + $props;
    }
}
