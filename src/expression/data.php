<?php

namespace phuety\expression;

use PhpParser\Node\Expr\Instanceof_;

class data {

    public function __construct(public array|object $data) {
    }

    public function get($leaf, $default = null) {
        if (!is_object($leaf)) return $leaf;
        if ($leaf instanceof node) {
            return $this->call($leaf, $default);
        }
        return match ($leaf->type) {
            'lit' => $leaf->value,
            'var' => $this->get_value($leaf->value, $default)
        };
    }

    public function get_value($path, $default = null) {
        # [$is_iteral, $val] = $this->check_literal($path);
        # if ($is_iteral) return $val;

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

    public function call($leaf, $default) {
        $meth = $leaf->value;
        if (!is_callable($meth)) return $default;
        return $meth(...array_map(fn ($e) => $this->get($e), $leaf->n));
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
