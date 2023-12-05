<?php
require __DIR__ . '/../vendor/autoload.php';

use phuety\expression\evaluator;
use phuety\expression\parser;
use phuety\expression\tokenstream;
use phuety\expression\data;

#$test = "req.method == 'GET' && date < now || rating in 1...3 && tomorrow && (has_feature || is_good)";
$test = "req.method == 'GET' && date < now || rating in good && tomorrow && (has_feature || is_good)";
#$test = "req.method == 'GET' && enddate ~ now && (date < now && ! (has_feature ||is_good)";
$test = "count(people, max(n, 99, 'ok')) > 5";
#$test = "count() > 5";
#$test = "count()";
#print_r(new tokenstream($test));
#exit;
#$test = "{name: 'otto', tags: ['light', 'dark'], big: count('all', max)>5,  age: \$age}";
$test = "tag in ['spring', 'sum' ~ 'mer', season()]";
$test = '`dreimal hoch auf ${name} juhu.`';
$parser = new parser(new tokenstream($test));

$res = $parser->parse();

print_r($res);
#exit;

$eval = new evaluator($res);
$data = new data([
    'req' => ['method' => "GET"],
    'date' => 4,
    'now' => 5,
    '$age' => 20,
    'count' => function (...$args) {
        print "count function here:";
        print_r($args);
        print "\n";
    },
    'tag' => 'winter',
    'season' => function () {
        print "season is winter\n";
        return 'winter';
    }
]);

// print_r($data->get('req.method'));

var_dump($eval->eval($data));
