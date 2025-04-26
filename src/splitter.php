<?php

namespace phuety;

use DOMXPath;
use DOM\Document;
use Dom\Element;

use function PHPUnit\Framework\isNull;

class splitter {

    public function __construct(public array $handler = [], public string $assets_base = "", public array $custom_tags = [], public array $opts = []) {
        $this->handler = [
            new handle_css(),
            new handle_script(),
            new handle_link()
        ];
        foreach ($custom_tags as $tag) {
            $this->handler[] = new handle_custom_tag($tag);
        }
    }

    /*
        i don't think it's possible to get php code via dom pi nodes
        atm only php block at the end of sfc is supported
        TODO: support more cases
    */
    public function split_php($source, $name): parts {
        [$sfc, $php] = explode('<?php', $source, 2) + [1 => ""];
        $php_start = $php ? count(explode("\n", $sfc)) : null;
        return new parts($name, rtrim($php, '>?'), trim($sfc), $php_start);
    }
    public function split_sfc(Document|string|null $dom, $name, bool $is_layout, parts $parts) {
        $parts->uid = $name . '---' . uniqid();

        // dom::d("split $name -- ", $dom);
        // TODO: scoping without uniqid
        if ($this->opts['css'] == 'scoped_simple') {
            $parts->uid = $name;
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
                        $parts->php = $phpcode;
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
                        // dbg("++ splitter", $node->tagName);
                        $handled = false;
                        foreach ($this->handler as $handler) {
                            if ($handler->handle($node, $parts)) {
                                if ($handler->remove_node) {
                                    $remove[] = $node;
                                }
                                $handled = true;
                                break;
                            }
                        }
                        // default: some template element
                        // if (!$handled) {}
                    }
                }
                /* sometimes code ends with ?> */
                // $php = rtrim($php, '>?');
                // $parts->php = $php;
            }
        }
        foreach ($remove as $node) {
            //$dom->documentElement->removeChild($node);
            $node->parentNode->removeChild($node);
        }
        // add class, only if scoped styles are needed
        if ($parts->css) {
            foreach ($dom->documentElement->childNodes as $node) {
                if ($node->nodeType == \XML_ELEMENT_NODE) {
                    dom::add_class($node, $parts->uid . ' root');
                }
            }
        }
        if ($is_layout) {
            $parts->dom = $dom;
        } else {
            // $parts['vue'] = $dom->saveHtml();
            $parts->dom = $dom;
        }

        $parts->is_layout = $is_layout;
        //if ($name == 'sc_navigation')
        // print_r($parts);
        return $parts;
    }
}
