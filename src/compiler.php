<?php

namespace phuety;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use WMDE\VueJsTemplating\Component as vcomponent;

class compiler {
    public array $compiled;
    public string $cbase;

    public function __construct(public string $base, public array $opts = ['css' => 'scope']) {
        $this->cbase = $base . '/../compiled';
    }

    public function get_component($name): component {
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
        $is_layout = false;
        $html = file_get_contents($this->base . '/' . $name . '.vue.php');
        if (
            str_starts_with($html, '<html') || str_starts_with($html, '<!DOCTYPE') ||
            str_starts_with($html, '<root') || str_starts_with($html, '<head') || str_starts_with($html, '<x-page')
        ) {
            $is_layout = true;
            // $dom = compiler::get_document($html);
            $dom = compiler::get_document($html);
        } else {
            $dom = compiler::get_fragment($html);
        }

        $parts = $this->split_sfc($dom, $name, $is_layout);
        $uid = component::create($name, $this->cbase, $parts);
        return $uid;
    }

    public function split_sfc(DOMDocument $dom, $name, $is_layout = false) {
        $parts = ['php' => "", 'vue' => "", 'css' => "", 'uid' => $name . '-' . uniqid()];
        if ($this->opts['css'] == 'scoped_simple') {
            $parts['uid'] = $name;
        }
        $remove = [];
        if ($is_layout) {
            // self::d("split layout", $dom);
            $pis = (new DOMXPath($dom))
                ->query('/processing-instruction("php")');
            // var_dump($pis);
            if ($pis->length > 0) {
                $pi = $pis->item(0);
                # var_dump($pi);
                $parts['php'] = rtrim((string)  $pi->nodeValue, '? ');
                $remove[] = $pi;
            }
        } else {

            // self::d("split component", $dom);
            $php = "";
            $php_open = false;
            foreach ($dom->documentElement->childNodes as $node) {
                if ($node->nodeType == \XML_PI_NODE) {
                    $phpcode = $node->nodeValue; #  rtrim((string) $node->nodeValue, '? ');
                    #$dom->documentElement->removeChild($node);
                    $lastchar = substr(rtrim($node->nodeValue), -1);
                    $php_open = ($lastchar != ';' && $lastchar != '?');
                    if ($lastchar == '?') {
                        $phpcode = rtrim((string) $node->nodeValue, '? ');
                    }
                    $php = $phpcode;
                    $remove[] = $node;
                } elseif ($node->nodeType == \XML_TEXT_NODE && $php && $php_open) {
                    $phpcode = $node->nodeValue;
                    $lastchar = substr(rtrim($node->nodeValue), -1);
                    $php_open = ($lastchar != ';' && $lastchar != '?');
                    if ($lastchar == '?') {
                        $phpcode = rtrim((string) $node->nodeValue, '? ');
                    }
                    $php .= '>' . $phpcode;
                    $remove[] = $node;
                } elseif ($node->nodeType == \XML_ELEMENT_NODE) {
                    if ($node->tagName == 'style') {
                        $parts['css'] = str_replace('root', '&.root', (string) $node->nodeValue);
                        $remove[] = $node;
                        #$dom->documentElement->removeChild($node);
                    } else {
                        // add class
                        self::add_class($node, $parts['uid'] . ' root');
                    }
                }
            }
            $parts['php'] = $php;
        }
        foreach ($remove as $node) {
            //$dom->documentElement->removeChild($node);
            $node->parentNode->removeChild($node);
        }
        if ($is_layout) {
            $parts['vue'] = $dom->saveHtml();
        } else {
            // $parts['vue'] = $dom->saveHtml();
            $parts['vue'] = substr(trim($dom->saveHtml()), 4, -5);
        }

        $parts['is_layout'] = $is_layout;

        // print_r($parts);
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
    static function add_class(DOMElement $node, $class) {
        if ($node->hasAttribute('class')) {
            $class = $node->getAttribute('class') . ' ' . $class;
        }
        $node->setAttribute('class', $class);
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
            $node->nodeValue .
            ' (level ' .  $level . ")\n";
        foreach ($child as $item) {
            self::dump($item, $level + 1);
        }
    }
}
