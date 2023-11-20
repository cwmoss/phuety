<?php

namespace phuety;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/*
    dom helper functions
*/

class dom {

    static public function attributes(DOMNode $node) {
        $attrs = [];
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $attrs[$attr->nodeName] = $attr->nodeValue;
            }
        }
        return $attrs;
    }
    static function add_class(DOMElement $node, $class) {
        if ($node->hasAttribute('class')) {
            $class = $node->getAttribute('class') . ' ' . $class;
        }
        $node->setAttribute('class', $class);
    }

    static function append_html(DOMNode $parent, $html) {
        $tmp = new DOMDocument();
        @$tmp->loadHTML("<meta http-equiv='Content-Type' content='charset=utf-8' /><ok>$html</ok>");

        foreach ($tmp->getElementsByTagName('ok')->item(0)->childNodes as $node) {
            $node = $parent->ownerDocument->importNode($node, true);
            $parent->appendChild($node);
        }
    }

    static function get_document($html) {
        $document = new DOMDocument();
        @$document->loadHTML($html);
        return $document;
    }

    static function get_fragment($html) {
        $document = new DOMDocument();
        @$document->loadHTML("<meta http-equiv='Content-Type' content='charset=utf-8' /><ok>$html</ok>");
        $dom = new DOMDocument();
        $first_div = $document->getElementsByTagName('ok')[0];
        $first_div_node = $dom->importNode($first_div, true);
        $dom->appendChild($first_div_node);
        return $dom;
    }

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
