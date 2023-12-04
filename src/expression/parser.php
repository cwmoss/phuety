<?php

namespace phuety\expression;

class parser {

    public $prec = [
        'in' => 10,
        '==' => 10,
        '!=' => 10,
        '<' => 10,
        '!' => 5,
        '&&' => 2,
        '||' => 1
    ];

    public function __construct(public tokenstream $stream) {
    }


    public function parse(int $minprec = 0) {
        $left = $this->stream->next();
        $node = $left->text;
        while ($this->stream->more()) {

            $peek = $this->stream->peek();
            if ($node == ')') {
                return $node;
            }
            if ($node == '(') {
                $rval = $this->parse(0);
                // $node = [$op, $node, $rval];
                $chk = $this->stream->next();
                if (($chk->text ?? null) != ')') {
                    throw new syntax_exception("missing closing brackets", $left, $this->stream->source);
                }
                return $rval;
            }
            if ($node == '!') {
                $op = '!';
                $rval = $this->parse(0);
                $node = [$op, $rval, null];
                // return $rval;
                break;
            }

            if ($peek->text == ')') {
                break;
            }
            $op_prec = $this->prec[$peek->text] ?? null;
            if ($op_prec == null) {
                throw new syntax_exception("unkown operator", $peek, $this->stream->source);
            }
            if ($op_prec < $minprec) {
                break;
            }
            $opt = $this->stream->next();
            $op = $opt->text;

            #if ($op == ')') {
            #    return $node;
            #}

            $rval = $this->parse($op_prec);

            $node = [$op, $node, $rval];
        }
        return $node;
    }

    function is_var($next, $current = null) {
        if ($current != '.' && $next && $next->text == '.') return true;
        if ($current == '.' && $next->id == 262) return true;
        return false;
    }

    function is_compare($token) {
        return in_array($token->id, [366, 60]);
    }

    function is_logic($token) {
        return in_array($token->id, [365]);
    }
}
