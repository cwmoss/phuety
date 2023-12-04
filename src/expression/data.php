<?php

namespace phuety\expression;

class data {

    public function __construct(public array|object $data) {
    }

    public function get($path, $default = null) {
        [$is_iteral, $val] = $this->check_literal($path);
        if ($is_iteral) return $val;

        if (!is_array($path)) $path = explode('.', $path);
        $val = $this->data;
        foreach ($path as $key) {
            if (is_array($val)) {
                if (!isset($val[$key])) {
                    return $default;
                } else {
                    $val = $val[$key];
                }
            }
            if (is_object($val)) {
                if (!isset($val->$key)) {
                    return $default;
                } else {
                    $val = $val->$key;
                }
            }
        }
        return $val;
    }

    public function check_literal($path) {
        if (!is_string($path)) {
            return [true, $path];
        }
        return match ($path[0]) {
            '"' => [true, substr($path, 1, -1)],
            "'" => [true, substr($path, 1, -1)],
            default => [false, $path]
        };
    }
}
