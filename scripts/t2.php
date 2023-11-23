<?php
require __DIR__ . '/../vendor/autoload.php';

use Le\SMPLang\SMPLang;

$test = '<? nested[`closure`]("hello \"again\"")(`worl(d)`)?:`no {$hey} ${meth()+"89"}` ?>';
$test = '<? address.line between 1..10 ?>';

$toks = expr_token::tokenize($test);

$skip = [389, 391];
$prev = $next = null;
foreach ($toks as $i => $token) {
    if ($i > 0) $prev = $toks[$i - 1];
    if (isset($toks[$i + 1])) $next = $toks[$i + 1];
    if (in_array($token->id, $skip)) {
        continue;
    }
    if ($token->text == '?') {
        if ($next->text == '?') {
            $token->text .= '?';
            $token->operator = true;
            unset($toks[$i + 1]);
        }
        if ($next->text == ':') {
            $token->text .= ':';
            $token->operator = true;
            unset($toks[$i + 1]);
        }
    }
}

print_r($toks);

class expr_token extends PhpToken {
    public bool $operator = false;
}


$t = '"hello wor ?: ld"';
$parser = new SMPLang(['strrev' => 'strrev']);

var_dump($parser->evaluate($t));
