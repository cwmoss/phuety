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
#$test = "tag in ['spring', 'sum' ~ 'mer', season()]";
#$test = '`dreimal hoch auf ${name} juhu.`';
#$test = '`eins zwo drei`';
#$test = "`eins zwo drei`";
#$test = '"foo"~1+2~`baz`';
$test = '(number - 10 * 4 / 2 - 3) % 10';
$test = '(100 + number * 1 <= 200 || number <-1) === negative';
$test = '(100 + number * 1 <= 200 || number <-1) === negative';
$test = 'true';
$test = '((true)&&false&&false||true&&true)';
$test = '((true)&&false&&false||true&&true)';
$test = 'true && false && (false || true) && true';
$test = 'true && false && false || true && true';
$test = '!false && !true && (false || !false) && true';
$test = '{first: "one", second: "two", key: 23}';
$test = 'object.method(10)';
#$test = '(a || b) && c';
#$test = "5-3-2";
print $test . "\n";
print_r(new tokenstream($test));


$parser = new parser(new tokenstream($test));

$res = $parser->parse();

print_r($res);
#exit;

$eval = new evaluator($res);
$data = new data([
    'number' => 123,
    'negative' => false,
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
    },
    'object' => new class() {
        public function method(int $number): int {
            return $number * 100;
        }
    },
]);

// print_r($data->get('req.method'));

var_dump($eval->eval($data));
