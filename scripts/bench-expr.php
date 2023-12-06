<?php

require __DIR__ . '/../vendor/autoload.php';

use Le\SMPLang\SMPLang;
use phuety\expression\parser;
use phuety\expression\evaluator;
use phuety\expression\data;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$test = "age > 18 && has_credit";
$test = "age > 20 || !has_credit || is_friend";
$data = ['age' => 19, 'has_credit' => true, 'is_friend' => true];

$exp = parser::new_from_string($test);
var_dump($exp->eval($data));

$exp = new SMPLang(['strrev' => 'strrev']);
var_dump($exp->evaluate($test, $data));

$exp = new ExpressionLanguage();
var_dump($exp->evaluate($test, $data));

# exit;

print "\n--\n";
print "eval expr with smplang\n";
$t1 = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $exp = new SMPLang(['strrev' => 'strrev']);
    $exp->evaluate($test, $data);
}
print_r(benchmark_time($t1));
print "memory: " . hum_size(memory_get_usage()) . "\n";

print "\n--\n";
print "eval expr with phuety\n";
$t1 = microtime(true);

for ($i = 0; $i < 1000; $i++) {
    $exp = parser::new_from_string($test);
    $exp->evaluate($test, $data);
}
print_r(benchmark_time($t1));
print "memory: " . hum_size(memory_get_usage()) . "\n";

print "\n--\n";
print "eval expr with phuety (cached expression)\n";
$t1 = microtime(true);
// $exp = new parser;
$exp = parser::new_from_string($test);
$res = $exp->parse();
for ($i = 0; $i < 1000; $i++) {
    # $exp = parser::new_from_string($test);
    # $exp->eval($test, $data);
    $eval = new evaluator($res);
    $eval->eval(new data($data));
}
print_r(benchmark_time($t1));
print "memory: " . hum_size(memory_get_usage()) . "\n";

print "\n--\n";
print "eval expr with symfony\n";
$t1 = microtime(true);
#$exp = new ExpressionLanguage();
for ($i = 0; $i < 1000; $i++) {
    $exp = new ExpressionLanguage();
    $exp->evaluate($test, $data);
}
print_r(benchmark_time($t1));
print "memory: " . hum_size(memory_get_usage()) . "\n";
// var_dump($exp);

print "\n--\n";
print "eval expr with symfony (cached expression)\n";
$t1 = microtime(true);
$exp = new ExpressionLanguage();
for ($i = 0; $i < 1000; $i++) {
    $exp->evaluate($test, $data);
}
print_r(benchmark_time($t1));
print "memory: " . hum_size(memory_get_usage()) . "\n";
// var_dump($exp);

function benchmark_time($start) {
    $elapsed = microtime(true) - $start;
    $time = [
        'time' => $elapsed,
        'ms' => (int)($elapsed * 1000),
        'microsec' => (int)($elapsed * 1000 * 1000),
        'print' => null
    ];
    $time['print'] = $time['ms'] ? $time['ms'] . ' ms' : $time['microsec'] . ' μs';
    return $time;
}

function hum_size($size) {
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}
