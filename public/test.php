<?php
print "huhu";
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
