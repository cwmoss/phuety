<?php

namespace phuety;

use DOMXPath;
use DOMDocument;

use function PHPUnit\Framework\isNull;

class splitter {

    public function __construct(public array $opts = [], public string $assets_base) {
    }

    /*
        i don't think it's possible to get php code via dom pi nodes
        atm only php block at the end of sfc is supported
        TODO: support more cases
    */
    public function split_php($source) {
        [$sfc, $php] = explode('<?php', $source, 2) + [1 => ""];
        return [$sfc, $php];
    }
    public function split_sfc(DOMDocument $dom, $name, $is_layout = false) {
        $parts = [
            'php' => "", 'vue' => "", 'css' => "", 'js' => [],
            'assets' => [],
            'uid' => $name . '---' . uniqid()
        ];
        // dom::d("split $name -- ", $dom);
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
                        $this->handle_css($node, $parts);
                        $remove[] = $node;
                        #$dom->documentElement->removeChild($node);
                    } else if ($node->tagName == 'script') {
                        $this->handle_script($node, $parts);
                        $remove[] = $node;
                    } else if ($node->tagName == 'link') {
                        $this->handle_link($node, $parts);
                        $remove[] = $node;
                    } else {
                        // add class
                        dom::add_class($node, $parts['uid'] . ' root');
                    }
                }
            }
            /* sometimes code ends with ?> */
            $php = rtrim($php, '>?');
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
        //if ($name == 'sc_navigation')
        // print_r($parts);
        return $parts;
    }

    public function handle_link($node, &$parts) {
        $attrs = dom::attributes($node);
        // if($attrs['href'])
        $parts['assets'][] = ['link', 'head', $attrs, $node->ownerDocument->saveHTML($node)];
    }

    public function handle_script($node, &$parts) {
        $attrs = dom::attributes($node);
        $position = (isset($attrs['head']) ? 'head' : null);
        if (is_null($position)) $position = 'body';
        $node->removeAttribute('head');
        // convert embeded to external?
        if (!isset($attrs['src'])) {
            $name = $parts['uid'] . '-' . count($parts['js']) . '.js';
            $parts['js'][$name] = (string) $node->nodeValue;
            $node->nodeValue = null;
            $node->setAttribute('src', '/assets/generated/' . $name);
        } else {
            // todo: cache buster
            if ($attrs['src'] ?? null && $attrs['src'][0] == '/') {
                $node->setAttribute('src', $attrs['src'] . '?' . time());
            }
        }
        $parts['assets'][] = ['script', $position, dom::attributes($node), $node->ownerDocument->saveHTML($node)];
    }

    public function handle_css($node, &$parts) {
        $attrs = dom::attributes($node);
        if (!isset($attrs['global'])) {
            $parts['css'] = str_replace('root', '&.root', (string) $node->nodeValue);
        } else {
            $node->removeAttribute('global');
            $parts['assets'][] = ['style', 'head', dom::attributes($node), $node->ownerDocument->saveHTML($node)];
        }
    }
}
