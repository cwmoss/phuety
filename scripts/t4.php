<?php
require __DIR__ . '/../vendor/autoload.php';

use phuety\bucket;
use phuety\props;
use Le\SMPLang\SMPLang;
use phuety\asset;

$data = new bucket;
$data->set_data(['name' => 'otto', 'person' => ['name' => 'inge']]);
$data->runes = ['$asset' => new asset];
$data->runes['$asset']->push('123', ['1', 'a', 'b', 'main.css']);

$parser = new SMPLang(['strrev' => 'strrev']);
print $parser->evaluate('name', $data);
print $parser->evaluate('person.name', $data);
//print $parser->evaluate('$asset.get', $data);
print $data['$asset']->get();
print "--end";
print_r($data);
