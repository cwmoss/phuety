<?php
require __DIR__ . '/../vendor/autoload.php';

$compiler = new slow\compiler(__DIR__ . '/../templates');

$c = $compiler->get_component('hello');
print_r($c);
$doc = $c->run();
print $doc->saveHTML();
exit;
print $document->saveHTML();

print "toplevel:";
fc($document->documentElement);

function fc($node, $level = 0) {
    if ($level > 0) return;
    $child = $node->childNodes;
    foreach ($child as $item) {
        $level++;
        print "node " . (property_exists($item, 'tagName') ? $item->tagName : 'x') . ' - ' . $item->nodeType . ' - ' . $level . "\n";
        if ($item->nodeType == XML_TEXT_NODE || $item->nodeType == XML_PI_NODE) {
            if (!trim($item->nodeValue)) continue;
            if (strlen(trim($item->nodeValue))) echo trim($item->nodeValue) . "<br/>";
        } else if ($item->nodeType == XML_ELEMENT_NODE) {
            fc($item, $level);
        }
    }
}
  
//$el = $document->getElementsByTagName('userprofile');
