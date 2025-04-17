<?php

namespace phuety;

class data_container {

    private array $blocks = [];

    public function __construct(private array $data, private array $helper = []) {
    }

    /*
        get and call are used for expression evaluation
    */
    public function call($name) {
        if (function_exists($name)) return $name;
        if (isset($this->helper[$name])) return $this->helper[$name];
        return $this->data[$name] ?? fn() => "unknown closure $name";
    }

    public function get($name, $default = null) {
        if ($this->blocks) {
            foreach (range(count($this->blocks) - 1, 0) as $idx) {
                if (isset($this->blocks[$idx][$name])) return $this->convert($this->blocks[$idx][$name]);
            }
        }
        return $this->convert($this->data[$name]) ?? $default;
    }

    public function __isset($name) {
        if ($this->blocks) {
            foreach (range(count($this->blocks) - 1, 0) as $idx) {
                if (isset($this->blocks[$idx][$name])) true;
            }
        }
        return isset($this->data[$name]);
    }

    public function convert($value) {
        if (is_array($value) && !array_is_list($value)) return (object) $value;
        return $value;
    }

    public function add_block($data) {
        $this->blocks[] = $data;
    }

    public function remove_block() {
        array_pop($this->blocks);
    }

    public function add_local(array $local_var_and_closures) {
        $this->data += $local_var_and_closures;
    }

    /*
        magic methods && bool converter are used in the php sections of SFC templates
    */

    public function __get(string $prop) {
        return $this->get($prop);
    }

    public function __call(string $method, $args) {
        dbg("++ helper call", $method, $this->helper);
        return call_user_func_array($this->helper[$method], $args);
    }
}
