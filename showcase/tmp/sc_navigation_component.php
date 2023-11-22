<?php

use phuety\component;


class sc_navigation_component extends component {
    public string $uid = "sc_navigation-655e63bc21f4c";
    public bool $is_layout = false;
    public string $name = "sc_navigation";

    function run_code($props){
        $active = $props['path'] ?? '/';

$navpoints = [
    ['url' => '/', 'title' => 'Home'],
    ['url' => '/about', 'title' => 'About Us'],
    ['url' => '/contact', 'title' => 'Contact']
];



        return get_defined_vars() + $props;
    }
}
