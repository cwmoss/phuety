<?php


$doc = new DOMDocument;
$doc->loadXML("<container><template>huhu <hello>world</hello></template></container>");
$template = $doc->documentElement->firstChild;
$repl = $template->childNodes;
// $template->replaceWith("beautiful", $doc->createElement("world"));
$template->replaceWith(...$repl);
echo $doc->saveXML();
