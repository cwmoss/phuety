<?php

namespace phuety;

use Dom\Element;

class compiler_options {

    public function __construct(
        public string $long_prefix = "ph-",
        public string $short_prefix = ":",
        public string $bool_prefix = "?",
        public string $if = "if",
        public string $else = "else",
        public string $elseif = "elseif",
        public string $for = "foreach",
        public string $bind = "bind",
        public string $html = "html"
    ) {
    }

    public function binding_prefixes() {
        return [
            $this->long_prefix . $this->bind . $this->short_prefix,
            $this->short_prefix,
        ];
    }

    public function check_attribute(Element $node, string $attr): bool|string {
        $test = [$this->long_prefix . $this->$attr];
        if ($this->short_prefix) $test[] = $this->short_prefix . $this->$attr;
        foreach ($test as $t) {
            if ($node->hasAttribute($t)) return $t;
        }
        return false;
    }
    public function check_and_remove_attribute(Element $node, string $attr): bool|string {
        $found = $this->check_attribute($node, $attr);
        if (!$found) return false;
        dbg("attr", $attr, $found);
        $attribute = $node->getAttribute($found);
        $node->removeAttribute($found);
        return $attribute;
    }
}
