<?php

namespace phuety\expression;

class helper {

    public static function dbg(...$vars) {
        print json_encode($vars) . "\n";
    }
}
