<?php

namespace phuety\expression;

class node {

    public ?array $n = null;

    public function __construct(public string $op, public  $lft = null, public  $rgt = null) {
    }

    public function is_leaf() {
        return in_array($this->op, ['var', 'lit']);
    }
}
