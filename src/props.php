<?php

namespace phuety;

class props {

    public array $data = [];

    public function get($uid = null) {
        return $this->data[$uid] ?? [];
    }

    public function set($uid, $key, $value) {
        if (isset($this->data[$uid])) {
            $this->data[$uid][$key] = $value;
        } else {
            $this->data[$uid] = [$key => $value];
        }
    }
}
