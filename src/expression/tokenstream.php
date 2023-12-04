<?php

namespace phuety\expression;

use PhpParser\Node\Stmt\Continue_;
use PhpToken;

class tokenstream {
    public string $source;
    public array $data;
    public int $index = -1;
    public int $maxindex = 0;
    public function __construct($code) {
        // remove spaces & php start tag
        $this->source = $code;
        // $this->data =  array_values(array_filter(PhpToken::tokenize('<?php ' . $code), fn ($tok) => !in_array($tok->id, [392, 389])));
        $this->data = $this->tokenize($code);
        $this->maxindex = (count($this->data) - 1);
    }

    public function tokenize(string $code) {
        $toks = PhpToken::tokenize('<?php ' . $code);
        $result = [];
        $br_symbols = [40 => 41, 123 => 125, 91 => 93]; // () {} []
        $br_open = array_keys($br_symbols);
        $br_close = array_values($br_symbols);
        $brackets = [];
        while ($tok = array_shift($toks)) {
            // remove white space & php start tag
            if (in_array($tok->id, [392, 389])) continue;
            if ($tok->id == 262) {
                $vartok = $tok;
                while ($toks && $this->is_var($toks[0], $tok->text)) {
                    $tok = array_shift($toks);
                    $vartok->text .= ($tok->text);
                }
                $result[] = $vartok;
                continue;
            }
            // number with dots to range? 1...10 => 1. . .3 => 1 ... 3
            if ($tok->id == '261') {
                if (
                    substr($tok->text, -1) == '.' &&
                    $toks[0] && $toks[0]->text == '.' &&
                    $toks[1] && $toks[1]->text[0] == '.'
                ) {
                    $tok->text = rtrim($tok->text, '.');
                    $result[] = $tok;
                    $tok = array_shift($toks);
                    $tok->text = '...';
                    $result[] = $tok;
                    $tok = array_shift($toks);
                    $tok->text = rtrim($tok->text, '.');
                    $result[] = $tok;
                } else {
                    $result[] = $tok;
                }
                continue;
            }
            if (in_array($tok->id, $br_open)) {
                $brackets[] = $tok;
            } elseif (in_array($tok->id, $br_close)) {
                $lastopen = array_pop($brackets);
                if (!$lastopen) {
                    throw new syntax_exception("missing open bracket for {$tok->text}", $tok, $this->source);
                }
                if ($br_symbols[$lastopen->id] != $tok->id) {
                    throw new syntax_exception("missing closing bracket for {$lastopen->text}", $lastopen, $this->source);
                }
            }
            $result[] = $tok;
        }
        if ($brackets) {
            $lastopen = array_pop($brackets);
            throw new syntax_exception("missing closing bracket for {$lastopen->text}", $lastopen, $this->source);
        }
        return $result;
    }

    function is_var($next, $current = null) {
        if ($current != '.' && $next && $next->text == '.') return true;
        if ($current == '.' && $next->id == 262) return true;
        return false;
    }

    public function next() {
        $this->index++;
        return $this->data[$this->index] ?? null;
    }
    public function current() {
        return $this->data[$this->index] ?? null;
    }
    public function peek() {
        return $this->data[$this->index + 1] ?? null;
    }

    public function foreward() {
        return [
            $this->data[$this->index] ?? null,
            $this->next(),
            $this->peek()
        ];
    }

    public function more() {
        return $this->index < $this->maxindex;
    }
}
