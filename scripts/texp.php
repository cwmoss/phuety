<?php
require __DIR__ . '/../vendor/autoload.php';

use phuety\expression\parser;
use phuety\expression\tokenstream;

$test = "req.method == 'GET' () && date < now 1...3 tomorrow && (has_feature || is_good)";
#$test = "req.method == 'GET' && enddate ~ now && (date < now && ! (has_feature ||is_good)";

print_r(new tokenstream($test));
exit;

$parser = new parser(new tokenstream($test));

$res = $parser->parse();

print_r($res);
