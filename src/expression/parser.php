<?php

namespace phuety\expression;

class parser {

    public $prec = [
        '**' => 40,
        '%' => 30,
        '/' => 30,
        '*' => 30,
        '-' => 25,
        '+' => 25,
        '~' => 20,
        'in' => 10,
        '===' => 10,
        '==' => 10,
        '!=' => 10,
        '<' => 10,
        '<=' => 10,
        '>' => 10,
        '>=' => 10,
        '!' => 35,
        '&&' => 2,
        '||' => 1,
        ':' => 0
    ];

    public function __construct(public ?tokenstream $stream = null, public array $data = []) {
    }

    public static function new_from_string($code): parser {
        return new static(new tokenstream($code));
    }

    public function evaluate($code, $data = []) {
        $this->stream = new tokenstream($code);
        return $this->eval($data);
    }

    public function eval($data) {
        $res = $this->parse();
        $eval = new evaluator($res);
        return $eval->eval(new data($data + $this->data));
    }

    public function parse(int $minprec = 0, $level = 0) {
        $left = $this->stream->next();
        // $node = $left->text;
        $node = leaf::new_from_token($left);
        print "parse-start -- $level -- {$left->text}\n";
        // start with operator?
        if ($left->text == '!') {
            # $minprec = $this->prec['!'];
        }
        while ($this->stream->more()) {

            $peek = $this->stream->peek();
            // for object args
            #if ($node->value == ',') {
            #    $this->stream->next();
            #    return $node;
            #}
            // for method args
            print "peek00: {$peek->text}\n";
            if ($peek->text === ',') {
                # $this->stream->next();
                return $node;
            }
            if ($node->value === ')') {
                print_r($node);
                print "return0 from )\n";
                return $node;
            }
            if ($node->value === '!') {
                $op = '!';
                $rval = $this->parse($this->prec[$op], $level + 1);
                $node = new node($op, $rval);
                // return $rval;
                continue;
            }
            // array?
            if ($node->value === '[') {
                $node = node::new_array($node);
                print "array start\n";
                while ($peek->text !== ']') {
                    $node->n[] = $this->parse(0, $level + 1);
                    print "object -- current:" . $this->stream->current() . "\n";
                    $peek = $this->stream->next();
                    // $peek = $this->stream->peek();
                }
                helper::dbg("++array+++", $node, $peek);
                // $this->stream->next();
                continue;
            }
            // object?
            if ($node->value === '{') {
                $node = node::new_object($node);
                print "object start\n";
                while ($peek->text != '}') {
                    $node->n[] = $this->parse(0, $level + 1);
                    print "object -- current:" . $this->stream->current() . "\n";
                    $peek = $this->stream->next();
                    // $peek = $this->stream->peek();
                }
                helper::dbg("++call+++", $node, $peek);
                $this->stream->next();
                continue;
            }
            // method?
            if ($peek->text === '(' && $node->value !== '(') {
                print "++call++";
                print_r($node);
                $node = node::new_call($node);
                helper::dbg($node);
                $this->stream->next();
                $peek = $this->stream->peek();
                while ($peek->text != ')') {
                    $node->n[] = $this->parse(0, $level + 1);
                    // $peek = $this->stream->next();
                    $peek = $this->stream->peek();
                    print "method -- peek:{$peek->text}\n";
                    if ($peek->text == ',') {
                        $this->stream->next();
                        $peek = $this->stream->peek();
                    }
                }
                helper::dbg("++call+++", $node, $peek);
                $this->stream->next();
                print "continue from method)\n";
                continue;
            }
            if ($node->value === '(') {
                print "start bracket $level\n";
                $node = $this->parse(0, $level + 1);
                print "end bracket $level\n";
                print_r($node);
                $this->stream->next();
                continue;
            }


            print "peek: {$peek->text}\n";
            if ($peek->text === ')' || $peek->text === '}' || $peek->text === ']') {
                print "break from ) $level\n";
                break;
            }
            $op_prec = $this->prec[$peek->text] ?? null;
            if ($op_prec === null) {
                helper::dbg('+ op failed', $node);
                throw new syntax_exception("unkown operator ({$peek->text})", $peek, $this->stream->source);
            }
            if ($op_prec <= $minprec) {
                break;
            }
            $opt = $this->stream->next();
            $op = $opt->text;

            // for objects
            #if ($op == ',') {
            #    return $node;
            #}
            #if ($op == ')') {
            #    return $node;
            #}
            print "peek2: {$peek->text}\n";
            $rval = $this->parse($op_prec, $level + 1);
            print "peek3: {$peek->text}\n";
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
