<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SerializedParsedExpression;

$expressionLanguage = new ExpressionLanguage();

#var_dump($expressionLanguage->evaluate('1 + 2')); // displays 3

#var_dump($expressionLanguage->compile('1 + 2')); // displays (1 + 2)

var_dump($expressionLanguage->evaluate("name == 'otto'", ['name' => 'otto']));

var_dump($expressionLanguage->evaluate("name", ['name' => 'otto']));

var_dump($expressionLanguage->parse("name == 'hu'", ['name']));

var_dump($expressionLanguage->parse("rev()", ['name', 'rev' => 'rev']));

exit;
$expression = new SerializedParsedExpression(
    "xname",
    serialize($expressionLanguage->parse("xname", ['xname' => 'otto'])->getNodes())
);
var_dump($expression);
var_dump($expressionLanguage->compile("xname", ['name' => 'otto']));
