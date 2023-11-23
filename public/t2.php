<?php

$dom = new DOMDocument('1.0', 'UTF-8');

$dom->loadHTML('<main if="arr.len > 3 && min==3"></main>');
echo $dom->saveXML();
$main = $dom->getElementsByTagName('main')->item(0);
print_r($main->attributes->item(0)->nodeValue);

$html = $dom->appendChild(new DOMElement('html'));
$body = $html->appendChild(new DOMElement('body'));
$pinode = new DOMProcessingInstruction('php', 'if($mode->name=="hello"){');
$piend = new DOMProcessingInstruction('php', '}');
//$body->appendChild($pinode);
//$body->setAttribute('title', $pinode);
$body->appendChild($pinode);
$main = new DOMElement('main');
$attr = $dom->createAttribute('__attrs');
$attr->value = "arr.len > 3 min==3";

$body->appendChild($main);
$main->appendChild($attr);
$body->appendChild($piend);
echo $dom->saveXML();
