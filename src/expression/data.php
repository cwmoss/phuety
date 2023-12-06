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
        #print_r($path);
        $val = $this->data;
        foreach ($path as $key) {
            if (is_array($val)) {
                if (!isset($val[$key])) {
                    #print "not-set $key";
                    return $default;
                } else {
                    #print "set $key";
                    $val = $val[$key];
                }
            } elseif (is_object($val)) {
                if (property_exists($val, $key) || method_exists($val, '__get')) {
                    $val = $val->$key;
                } elseif (method_exists($val, $key) || method_exists($val, '__call')) {
                    $val = \Closure::fromCallable([$val, $key]);
                } else {
                    return $default;
                }
            }
        }
        #print "data.get";
        #var_dump($val);
        return $val;
    }

    public function call($meth, $args, $default = null) {
        #print "data call $meth\n";
        #print_r($this->data);
        # $path = explode('.', $meth);
        $meth = $this->get_value($meth);
        #var_dump($meth);
        if (!is_callable($meth)) return $default;
        return $meth(...$args);
    }

    public function xcall($leaf, $default) {
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
