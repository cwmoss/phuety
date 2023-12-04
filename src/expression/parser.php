<?php

namespace phuety\expression;

class parser {

    public $prec = [
        'in' => 10,
        '==' => 10,
        '!=' => 10,
        '<' => 10,
        '>' => 10,
        '!' => 5,
        '&&' => 2,
        '||' => 1
    ];

    public function __construct(public tokenstream $stream) {
    }


    public function parse(int $minprec = 0) {
        $left = $this->stream->next();
        // $node = $left->text;
        $node = new leaf('', $left->text);
        while ($this->stream->more()) {

            $peek = $this->stream->peek();
            if ($peek->text == ',') {
                $this->stream->next();
                return $node;
            }
            if ($node->value == ')') {
                return $node;
            }
            // method?
            if ($peek->text == '(') {
                print "++call++";
                $node = node::new_call($node);
                helper::dbg($node);
                $this->stream->next();
                $peek = $this->stream->peek();
                while ($peek->text != ')') {
                    $node->n[] = $this->parse(0);
                    $peek = $this->stream->peek();
                }
                helper::dbg("++call+++", $node, $peek);
                $this->stream->next();
                continue;
            }
            if ($node->value == '(') {
                print "start bracket\n";
                $rval = $this->parse(0);
                return $rval;
            }
            if ($node->value == '!') {
                $op = '!';
                $rval = $this->parse(0);
                $node = new node($op, $rval);
                // return $rval;
                break;
            }

            if ($peek->text == ')') {
                break;
            }
            $op_prec = $this->prec[$peek->text] ?? null;
            if ($op_prec == null) {
                helper::dbg('+ op failed', $node);
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

            $node = new node($op, $node, $rval);
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
