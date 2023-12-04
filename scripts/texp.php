<?php
require __DIR__ . '/../vendor/autoload.php';

use phuety\expression\evaluator;
use phuety\expression\parser;
use phuety\expression\tokenstream;
use phuety\expression\data;

#$test = "req.method == 'GET' && date < now || rating in 1...3 && tomorrow && (has_feature || is_good)";
$test = "req.method == 'GET' && date < now || rating in good && tomorrow && (has_feature || is_good)";
#$test = "req.method == 'GET' && enddate ~ now && (date < now && ! (has_feature ||is_good)";

#print_r(new tokenstream($test));
#exit;

$parser = new parser(new tokenstream($test));

$res = $parser->parse();

print_r($res);

$eval = new evaluator($res);
$data = new data([
    'req' => ['method' => "GET"],
    'date' => 4,
    'now' => 5,

]);

print_r($data->get('req.method'));

var_dump($eval->eval($data));
