<?php

namespace slow;

use DOMDocument;
use DOMNode;
use WMDE\VueJsTemplating\Component as vcomponent;

class compiler {
    public array $compiled;
    public string $cbase;

    public function __construct(public string $base) {
        $this->cbase = $base . '/../compiled';
    }

    public function get_component($name) {
        $cname = $name . '_component';
        if ($this->compiled[$name] ?? null) {
            $comp = $this->compiled[$name];
        } else {
            $uid = $this->compile($name);
            $comp = $this->load($name);
        }
        return $comp;
    }

    public function xxxxrun($component) {
    }

    public function load($name) {
        $cname = $name . '_component';
        $comp = component::load($name, $this->cbase);
        $comp->compiler = $this;
        $this->compiled[$name] = $comp;
        return $comp;
    }


    public function compile($name) {
        $sfc = file_get_contents($this->base . '/' . $name . '.sfc');
        $dom = self::get_fragment($sfc);

        $parts = $this->split_sfc($dom);
        $uid = component::create($name, $this->cbase, $parts, $name . '-' . uniqid());
        return $uid;
    }

    public function split_sfc(DOMDocument $dom) {
        $parts = ['php' => "", 'vue' => "", 'css' => ""];
        $remove = [];
        foreach ($dom->documentElement->childNodes as $node) {
            if ($node->nodeType == \XML_PI_NODE) {
                $parts['php'] = rtrim((string) $node->nodeValue, '? ');
                #$dom->documentElement->removeChild($node);
                $remove[] = $node;
            } elseif ($node->nodeType == \XML_ELEMENT_NODE) {
                if ($node->tagName == 'style') {
                    $parts['css'] = (string) $node->nodeValue;
                    $remove[] = $node;
                    #$dom->documentElement->removeChild($node);
                } else {
                    // add class
                }
            }
        }
        foreach ($remove as $node) {
            $dom->documentElement->removeChild($node);
        }
        $parts['vue'] = substr(trim($dom->saveHtml()), 5, -6);

        return $parts;
    }
    static public function attributes(DOMNode $node) {
        $attrs = [];
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $attrs[$attr->nodeName] = $attr->nodeValue;
            }
        }
        return $attrs;
    }
    public function add_class($node, $class) {
    }

    static function get_fragment($html) {
        $document = new DOMDocument();
        @$document->loadHTML("<meta http-equiv='Content-Type' content='charset=utf-8' /><div>$html</div>");
        $dom = new DOMDocument();
        $first_div = $document->getElementsByTagName('div')[0];
        $first_div_node = $dom->importNode($first_div, true);
        $dom->appendChild($first_div_node);
        return $dom;
    }
}
