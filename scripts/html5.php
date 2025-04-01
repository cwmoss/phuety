<?php

$html = <<<'html'
<template><template>
<tr hidden data-ref="<?=$id?>"><td>okok</td>
<?php print $dieid; ?></tr>
</template></template>
html;

$flags = LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR; //  | Dom\HTML_NO_DEFAULT_NS;
$dom = Dom\HTMLDocument::createFromString($html, $flags);

var_dump($dom->firstChild->getAttributeNode("hidden")->nodeValue);
print $dom->saveHtml();
print $dom->querySelector("template");
// var_dump($dom->firstChild->)
print "\n\n";
