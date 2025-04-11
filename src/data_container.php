<?php

namespace phuety;

class data_container {

    private array $blocks = [];

    public function __construct(private array $data, private array $helper = []) {
    }

    public function call($name) {
        if (function_exists($name)) return $name;
        return $this->data[$name] ?? fn() => "unknown closure $name";
    }

    public function get($name, $default = "") {
        if ($this->blocks) {
            foreach (range(count($this->blocks) - 1, 0) as $idx) {
                if (isset($this->blocks[$idx][$name])) return $this->blocks[$idx][$name];
            }
        }
        return $this->data[$name] ?? $default;
    }

    public function add_block($data) {
        $this->blocks[] = $data;
    }

    public function remove_block() {
        array_pop($this->blocks);
    }
}
