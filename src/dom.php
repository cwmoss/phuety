<?php

namespace phuety;

use DOM\Document;
use Dom\DocumentFragment;
use DOM\Element;
use Dom\HTMLDocument;
use Dom\HTMLElement;
use DOM\Node;
use DOMXPath;

/*
    dom helper functions
*/

class dom {

    static public function attributes(Node $node) {
        $attrs = [];
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $attrs[$attr->nodeName] = $attr->nodeValue;
            }
        }
        return $attrs;
    }
    static function add_class(Element $node, $class) {
        if ($node->hasAttribute('class')) {
            $class = $node->getAttribute('class') . ' ' . $class;
        }
        $node->setAttribute('class', $class);
    }

    static function register_class($dom) {
        // $dom->registerNodeClass(DOMElement::class, custom_domelement::class);
    }

    static function append_html(Node $parent, $html) {
        $tmp = self::get_template_fragment($html);

        foreach ($tmp->childNodes as $node) {
            $node = $parent->ownerDocument->importNode($node, true);
            $parent->appendChild($node);
        }
    }

    static function get_empty_doc() {
        // return HTMLDocument::createEmpty();
        return HTMLDocument::createFromString("<!DOCTYPE html><body>");
    }

    static function get_document($html) {
        $parserFlags = LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR; // | Dom\HTML_NO_DEFAULT_NS
        $document = HTMLDocument::createFromString($html, $parserFlags);
        return $document;
    }

    static function get_template_fragment(string $html) {
        $parserFlags = LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR; // | Dom\HTML_NO_DEFAULT_NS
        $document = HTMLDocument::createFromString("$html", $parserFlags);
        // $f = $document->createDocumentFragment();

        // var_dump($document->documentElement);
        // $f->append("<template>$html</template>");
        foreach ($document->childNodes as $part) {
            // print "PART- \n";
            // var_dump($part);
            // print "/PART \n\n";
        }
        // print "doc-- " . $document->saveHtml() . "---/doc";
        return $document;
    }
    /*
    static function get_fragment($html) {
        $document = new DOMDocument();
        self::register_class($document);
        @$document->loadHTML("<meta http-equiv='Content-Type' content='charset=utf-8' /><ok>$html</ok>");
        $dom = new DOMDocument();
        self::register_class($dom);
        $first_div = $document->getElementsByTagName('ok')[0];
        $first_div_node = $dom->importNode($first_div, true);
        $dom->appendChild($first_div_node);
        return $dom;
    }
*/
    static function d($descr, $dom) {
        print "$descr -- start --\n";
        self::dump($dom);
        print "$descr -- end --\n";
    }

    static function dump($node, $level = 0) {
        static $types = [1 => 'el', 2 => 'attr', 3 => 'txt', 4 => 'cdata', 7 => 'pi', 8 => 'com', 9 => 'doc', 10 => 'doctype', 13 => 'html'];
        $child = $node->childNodes;
        print str_repeat(' ', $level * 2) . " " . ($types[$node->nodeType] ?? $node->nodeType) .
            " " . (property_exists($node, 'tagName') ? $node->tagName : 'n.a.') .
            ' ' . $node->nodeValue .
            ' (level ' .  $level . ")\n";
        foreach ($child as $item) {
            self::dump($item, $level + 1);
        }
    }
}
