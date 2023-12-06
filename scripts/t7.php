<?php

class test {

    function __get($prop) {
        return $prop;
    }
}

$t = new test;
$key = 'ttt';
var_dump(isset($t->$key));


print $t->$key;
