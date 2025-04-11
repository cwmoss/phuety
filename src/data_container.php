<?php

namespace phuety;

class data_container {

    private array $blocks = [];

    public function __construct(private array $data, private array $helper = []) {
    }

    public function get($name, $default = "") {
        if ($this->blocks) {
            foreach (range(count($this->blocks) - 1, 0) as $b) {
                if (isset($b[$name])) return $b[$name];
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
