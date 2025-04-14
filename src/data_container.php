<?php

namespace phuety;

class data_container {

    private array $blocks = [];

    public function __construct(private array $data, private array $helper = []) {
    }

    public function call($name) {
        if (function_exists($name)) return $name;
        if (isset($this->helper[$name])) return $this->helper[$name];
        return $this->data[$name] ?? fn() => "unknown closure $name";
    }

    public function get($name, $default = "") {
        if ($this->blocks) {
            foreach (range(count($this->blocks) - 1, 0) as $idx) {
                if (isset($this->blocks[$idx][$name])) return $this->convert($this->blocks[$idx][$name]);
            }
        }
        return $this->convert($this->data[$name]) ?? $default;
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
}
