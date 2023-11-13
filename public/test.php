<?php
require __DIR__ . '/../vendor/autoload.php';

use slow\compiler;

print "huhu";
$dom = get_fragment('<p-ok><h2>huh</h2></p-ok>');
compiler::dump($dom);
print $dom->saveHTML();

$dom = new DOMDocument();
$el = $dom->createTextNode('<p>hello!</p>');
$dom->appendChild($el);
print $dom->saveHTML();

$html = '<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ props.title }}</title>

</head>
<body>
<h1>{{smile}}</h1>
<slot></slot>
</body></html>';

$dom = new DOMDocument();
$dom->loadHTML($html);
$dom->is_page = true;
print $dom->saveHTML();
print_r($dom->documentElement);
var_dump($dom->is_page);
exit;

function get_fragment($html) {
    $document = new DOMDocument();
    @$document->loadHTML("<meta http-equiv='Content-Type' content='charset=utf-8' /><di>$html</di>");
    $dom = new DOMDocument();
    $first_div = $document->getElementsByTagName('di')[0];
    $first_div_node = $dom->importNode($first_div, true);
    $dom->appendChild($first_div_node);
    return $dom;
}

$document = new DOMDocument();
$document->loadHTMLFile(__DIR__ . '/../templates/hello.sfc');
print_r($document);
print_r($document->doctype);

print $document->saveHTML();

$el = $document->getElementsByTagName('userprofile');
print_r($el[0]);
$p = $el[0];
print $p->getNodePath();
#exit;
foreach ($p->attributes as $attr) {
    $name = $attr->nodeName;
    $value = $attr->nodeValue;
    echo "Attribute '$name' :: '$value'<br />";
}

$s2 = file_get_contents(__DIR__ . '/../templates/hello.sfc');
$dom2 = $document->createDocumentFragment();
$dom2->appendXML($s2);
print_r($dom2);
