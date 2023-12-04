<?php

namespace phuety\expression;

class leaf {

    public ?string $quote = null;

    public function __construct(public string $type, public string $value) {
        if ($type === '') {
            [$is_literal, $lit, $quote] = $this->is_literal($value);
            if ($is_literal) {
                $this->type = 'lit';
                $this->value = $lit;
                $this->quote = $quote;
            } else {
                $this->type = 'var';
            }
        }
    }


    public function is_literal($exp) {
        if (!is_string($exp)) {
            return [false, null, null];
        }
        return match ($exp[0]) {
            '"' => [true, substr($exp, 1, -1), '"'],
            "'" => [true, substr($exp, 1, -1), "'"],
            default => [false, $exp, null]
        };
    }
}
