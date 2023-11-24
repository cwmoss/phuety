<?php

namespace phuety;

use ArrayAccess;

class bucket implements ArrayAccess {
    private array $data = [];
    private array $context = [];
    public array $runes = [];

    public function set_data($d) {
        $this->data = $d;
    }

    public function __get($key) {
        if ($key[0] == '$') {
            if (isset($runes[$key])) {
                return $runes[$key];
            }
            return null;
        }
        if (isset($data[$key])) {
            return $data[$key];
        } else {
            return null;
        }
    }

    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool {
        if ($offset[0] == '$') {
            return isset($this->runes[$offset]);
        }
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset): void {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset): mixed {
        if ($offset[0] == '$') {
            return isset($this->runes[$offset]) ? $this->runes[$offset] : null;
        }
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
