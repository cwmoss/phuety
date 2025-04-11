<?php

namespace phuety;

class dotdata {

    public function __construct(public array $helper = []) {
    }

    public function evaluate($path, $data, $default = "") {
        $val = $data;
        $path = explode(".", $path);
        foreach ($path as $key) {
            if (!isset($val[$key])) {
                return $default;
            }
            $val = $val[$key];
        }
        return $val;
    }

    public function get($data, $path, $default = null) {
        $val = $data;
        $path = explode(".", $path);
        foreach ($path as $key) {
            if (!isset($val[$key])) {
                return $default;
            }
            $val = $val[$key];
        }
        return $val;
    }
}
