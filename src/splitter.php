<?php

namespace phuety;

use DOMXPath;
use DOM\Document;
use Dom\Element;

use function PHPUnit\Framework\isNull;

class splitter {

    public function __construct(public array $opts = [], public string $assets_base = "") {
    }

    /*
        i don't think it's possible to get php code via dom pi nodes
        atm only php block at the end of sfc is supported
        TODO: support more cases
    */
    public function split_php($source) {
        [$sfc, $php] = explode('<?php', $source, 2) + [1 => ""];
        return [trim($sfc), $php];
    }
    public function split_sfc(Document|string|null $dom, $name, $is_layout = false) {
        $parts = [
            'php' => "",
            'html' => "",
            'css' => "",
            'js' => [],
            'assets' => [],
            'uid' => $name . '---' . uniqid()
        ];
        // dom::d("split $name -- ", $dom);
        if ($this->opts['css'] == 'scoped_simple') {
            $parts['uid'] = $name;
        }
        $remove = [];
        if ($dom) {
            if ($is_layout) {
                // self::d("split layout", $dom);
                // var_dump(iterator_to_array($dom->childNodes));
                // die();
                // dbg("split layout", iterator_to_array($dom->childNodes));
                foreach ($dom->childNodes as $node) {
                    if ($node->nodeType == \XML_COMMENT_NODE && str_starts_with($node->textContent, "?php")) {
                        $phpcode = substr($node->textContent, 4);
                        $parts['php'] = $phpcode;
                        $remove[] = $node;
                    }
                }
                // var_dump(iterator_to_array($dom->childNodes));
                // die();
            } else {

                // self::d("split component", $dom);
                $php = "";
                $php_open = false;
                foreach ($dom->documentElement->childNodes as $node) {
                    // print_r($node);
                    if ($node->nodeType == \XML_COMMENT_NODE && str_starts_with($node->textContent, "?php")) {
                        $phpcode = substr($node->textContent, 4); #  rtrim((string) $node->nodeValue, '? ');
                        #$dom->documentElement->removeChild($node);
                        //$lastchar = substr(rtrim($node->nodeValue), -1);
                        //$php_open = ($lastchar != ';' && $lastchar != '?');
                        //if ($lastchar == '?') {
                        //    $phpcode = rtrim((string) $node->nodeValue, '? ');
                        //}
                        $php = $phpcode;
                        $remove[] = $node;
                        continue;
                    }
                    if ($node->nodeType == \XML_ELEMENT_NODE) {
                        dbg("++ splitter", $node->tagName);
                        if ($node->tagName == 'STYLE') {
                            $this->handle_css($node, $parts);
                            $remove[] = $node;
                            #$dom->documentElement->removeChild($node);
                        } else if ($node->tagName == 'SCRIPT') {
                            $this->handle_script($node, $parts);
                            $remove[] = $node;
                        } else if ($node->tagName == 'LINK') {
                            $this->handle_link($node, $parts);
                            $remove[] = $node;
                        } else {
                            // add class
                            dom::add_class($node, $parts['uid'] . ' root');
                        }
                    }
                }
                /* sometimes code ends with ?> */
                // $php = rtrim($php, '>?');
                $parts['php'] = $php;
            }
        }
        foreach ($remove as $node) {
            //$dom->documentElement->removeChild($node);
            $node->parentNode->removeChild($node);
        }
        if ($is_layout) {
            $parts['html'] = $dom;
        } else {
            // $parts['vue'] = $dom->saveHtml();
            $parts['html'] = $dom;
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

    public function handle_script(Element $node, &$parts) {
        $attrs = dom::attributes($node);
        $position = (isset($attrs['head']) ? 'head' : null);
        if (is_null($position)) $position = 'body';
        $node->removeAttribute('head');
        // convert embeded to external?
        if (!isset($attrs['src'])) {
            dbg("++ js embed => external");
            $name = $parts['uid'] . '-' . count($parts['js']) . '.js';
            $parts['js'][$name] = (string) $node->textContent;
            dbg("+++ embed js $name", $parts['js'][$name]);
            $node->textContent = null;
            $node->setAttribute('src', '/assets/generated/' . $name);
        } else {
            // todo: cache buster
            if ($attrs['src'] ?? null && $attrs['src'][0] == '/') {
                $node->setAttribute('src', $attrs['src'] . '?' . time());
            }
        }
        $parts['assets'][] = ['script', $position, dom::attributes($node), $node->ownerDocument->saveHTML($node)];
    }

    public function handle_css(Element $node, &$parts) {
        $attrs = dom::attributes($node);
        if (!isset($attrs['global'])) {
            $parts['css'] = str_replace('root', '&.root', (string) $node->textContent);
        } else {
            $node->removeAttribute('global');
            $parts['assets'][] = ['style', 'head', dom::attributes($node), $node->ownerDocument->saveHTML($node)];
        }
    }
}
