<?php

use phuety\component;
use Leaf\Fetch;


class sc_team_component extends component {
    public string $uid = "sc_team-655f24d011e77";
    public bool $is_layout = false;
    public string $name = "sc_team";

    function run_code($props){
        
$res = Fetch::get("https://randomuser.me/api/?results=10");


        return get_defined_vars() + $props;
    }
}
