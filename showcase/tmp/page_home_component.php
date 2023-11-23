<?php

use phuety\component;


class page_home_component extends component {
    public string $uid = "page_home-655f221e9b73d";
    public bool $is_layout = false;
    public string $name = "page_home";

    function run_code($props){
        $title = "startseite!";
$name = "welt";
$ok = true;
$user = '1234';

        return get_defined_vars() + $props;
    }
}
