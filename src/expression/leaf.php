<?php

namespace phuety\expression;

class leaf {

    public ?string $quote = null;

    public function __construct(public string $type, public string|bool|null $value) {
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

    public static function new_from_token($token) {
        // T_LNUMBER
        if ($token->id == \T_LNUMBER || $token->id == \T_DNUMBER) {
            return new self('lit', $token->text);
        }
        $val = match ($token->text) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => ''
        };
        if ($val !== '') {
            return new self('lit', $val);
        }
        return new self('', $token->text);
    }

    public function is_literal($exp) {
        if (!is_string($exp)) {
            return [false, null, null];
        }
        return match ($exp[0]) {
            '"' => [true, substr($exp, 1, -1), '"'],
            "'" => [true, substr($exp, 1, -1), "'"],
            '`' => [true, substr($exp, 1, -1), '`'],
            default => [false, $exp, null]
        };
    }
}
