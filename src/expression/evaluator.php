<?php

namespace phuety\expression;



class evaluator {

    public function __construct(public $expression) {
    }

    public function eval($data) {
        $exp = $this->expression;
        if ($exp instanceof node) {
            return $this->evaluate($exp, $data);
        } else {
            // expression is just a leaf
            return $data->get($this->expression);
        }
    }

    public function evaluate($exp, $data) {
        if ($exp instanceof leaf) {
            return $exp;
        }
        if (is_array($exp->n)) {
            $lft = array_map(fn ($n) => $this->evaluate($n, $data), $exp->n);
        } elseif ($exp->value instanceof node) {
            $lft = $this->evaluate($exp->value, $data);
        } else {
            $lft = $exp->value;
        }
        if ($exp->op == '&&' && !$lft) return false;
        if ($exp->op == '||' && $lft) return true;
        if ($exp->rgt instanceof node) {
            $rgt = $this->evaluate($exp->rgt, $data);
        } else {
            $rgt = $exp->rgt;
        }
        //var_dump(['op', $exp->op, $lft, $rgt]);
        $res = $this->eval_op($exp->op, $lft, $rgt, $data);
        // var_dump(['res', $exp->op, $res]);
        return $res;
    }

    public function eval_op($op, $lft, $rgt, $data) {
        // $lft = $data->get($lft);

        return match ($op) {
            "==" => $data->get($lft) == $data->get($rgt),
            "===" => $data->get($lft) === $data->get($rgt),
            "!=" => $data->get($lft) != $data->get($rgt),
            "<" => $data->get($lft) < $data->get($rgt),
            ">" => $data->get($lft) > $data->get($rgt),
            "<=" => $data->get($lft) <= $data->get($rgt),
            ">=" => $data->get($lft) >= $data->get($rgt),
            "&&" => $data->get($lft) && $data->get($rgt),
            "||" => $data->get($lft) || $data->get($rgt),
            "!" => !$data->get($lft),
            "in" => in_array($data->get($lft), $data->get($rgt, [])),
            "~" => $data->get($lft) . $data->get($rgt),
            ":" => [$lft->value, $data->get($rgt)],
            "call" => $this->eval_call($rgt, $lft, $data),
            "array" => $this->eval_array($lft, $data),
            "object" => $this->eval_object($lft, $data),
            "+" => $data->get($lft) + $data->get($rgt),
            "-" => $data->get($lft) - $data->get($rgt),
            "*" => $data->get($lft) * $data->get($rgt),
            "/" => $data->get($lft) / $data->get($rgt),
            "%" => $data->get($lft) % $data->get($rgt),
            "**" => $data->get($lft) ** $data->get($rgt),
        };
    }

    public function eval_array($items, $data) {
        $res = array_map(fn ($e) => $data->get($e), $items);
        print_r($res);
        return $res;
    }

    public function eval_call($meth, $args, $data) {
        print "calling $meth\n";
        $args = array_map(fn ($e) => $data->get($e), $args);
        return $data->call($meth, $args);
    }

    public function eval_object($els, $data) {
        var_dump($els);
        $o = [];
        foreach ($els as $l) {
            $o[$l[0]] = $l[1];
        }
        return $o;
    }
    public function xevaluate($exp, $data) {
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

    public function xeval_op($op, $lft, $rgt, $data) {
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
