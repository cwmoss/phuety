<?php

namespace phuety\expression;

class node {

    public ?array $n = null;

    public function __construct(public string $op, public  $value = null, public  $rgt = null) {
        if (false && $op === '') {
            [$is_literal, $lit] = $this->is_literal($lft);
            if ($is_literal) {
                $this->op = 'lit';
                $this->lft = $lit;
            } else {
                $this->op = 'var';
            }
        }
    }

    public static function new_call(leaf $leaf) {
        $node = new self('call', $leaf->value);
        $node->n = [];
        return $node;
    }

    public function is_leaf() {
        return in_array($this->op, ['var', 'lit']);
    }
}
