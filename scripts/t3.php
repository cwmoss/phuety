<?php

use phuety\custom_domelement;
use phuety\props;

require __DIR__ . '/../vendor/autoload.php';

$dom = new DOMDocument();
$dom->registerNodeClass(DOMElement::class, custom_domelement::class);

$el = $dom->createElement('div');
$el->data = new props;
$el->data->set('test', 'you', 'me');

$dom->appendChild($el);
print $dom->saveHTML();
$dom->documentElement->hey(" save!");
print_r($dom->documentElement);
print_r($dom->documentElement->data);

print $el->hey();
print "el2\n";
$el2 = $el->cloneNode(true);
print_r($el2);
$el2->hey();

$dom2 = $dom->cloneNode(true);
$dom2->registerNodeClass(DOMElement::class, custom_domelement::class);
print $dom2->saveHTML();
print_r($dom2->documentElement);
$dom2->documentElement->hey();

$el3 = new custom_domelement('em');
$el3->hey();
