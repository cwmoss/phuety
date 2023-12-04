<?php

namespace phuety\expression;

class evaluator {

    public function __construct(public $expression) {
    }

    public function eval($data) {
        $exp = $this->expression;
        return $this->evaluate($exp, $data);
    }

    public function evaluate($exp, $data) {
        [$op, $lft, $rgt] = $exp;
        if (is_array($lft)) {
            $lft = $this->evaluate($lft, $data);
        }
        var_dump($lft);
        if ($op == '&&' && !$lft) return false;
        if ($op == '||' && $lft) return true;

        if (is_array($rgt)) {
            $rgt = $this->evaluate($rgt, $data);
        }
        var_dump($rgt);
        $res = $this->eval_op($op, $lft, $rgt, $data);
        var_dump(['res', $op, $res]);
        return $res;
    }

    public function eval_op($op, $lft, $rgt, $data) {
        return match ($op) {
            "==" => $data->get($lft) == $data->get($rgt),
            "!=" => $data->get($lft) != $data->get($rgt),
            "<" => $data->get($lft) < $data->get($rgt),
            ">" => $data->get($lft) > $data->get($rgt),
            "&&" => $data->get($lft) && $data->get($rgt),
            "||" => $data->get($lft) > $data->get($rgt),
            "!" => !$data->get($lft),
            "in" => in_array($data->get($lft), $data->get($rgt, [])),
        };
    }
}
