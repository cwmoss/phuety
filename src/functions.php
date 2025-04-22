<?php

namespace phuety;

function dbg(...$args) {
    error_log(json_encode($args, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 4);
}
