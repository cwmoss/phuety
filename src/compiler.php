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


    public function __construct(public phuety $engine) {
        $this->cbase = $engine->cbase;
    }

    public function compile($name, $source) {
        $is_layout = false;
        if (
            str_starts_with($source, '<html') || str_starts_with($source, '<!DOCTYPE') ||
            str_starts_with($source, '<root') || str_starts_with($source, '<head') || str_starts_with($source, '<x-page')
        ) {
            $is_layout = true;
            // $dom = compiler::get_document($html);
            $dom = dom::get_document($source);
        } else {
            $dom = dom::get_fragment($source);
        }

        $parts = $this->split_sfc($dom, $name, $is_layout);
        $uid = $this->create_component($name, $parts);
        // $uid = component::create($name, $this->cbase, $parts);
        return $uid;
    }

    public function create_component($name, $parts) {
        # print "create component $name";
        // print_r($parts);
        $tpl = file_get_contents(__DIR__ . '/_component.php');
        $dir = $this->cbase;
        [$php, $use] = $this->get_use_statements($parts['php']);
        $repl = [
            'NAME' => $name, 'UID' => $parts['uid'],
            'ISLAYOUT' => $parts['is_layout'] ? 'true' : 'false',
            'PHPCODE' => $php,
            'USESTATEMENTS' => $use
        ];

        $tpl = str_replace(array_keys($repl), array_values($repl), $tpl);
        file_put_contents($dir . '/' . $name . '_component.php', $tpl);
        $css = sprintf(".%s{\n%s\n}", $parts['uid'], $parts['css']);
        file_put_contents($dir . '/' . $name . '.css', $css);
        // $php = '<?php ' . $parts['php'];
        // file_put_contents($dir . '/' . $name . '.run.php', $php);
        $vue = sprintf('%s', $parts['vue']);
        file_put_contents($dir . '/' . $name . '.html', $vue);
        return $repl['UID'];
    }

    public function get_use_statements($code) {
        $use = preg_match_all("/^\s*use\s+[^;]+;\s*$/ms", $code, $mat, \PREG_SET_ORDER);
        if (!$mat) return [$code, ""];

        $use = join("\n", array_map(fn ($el) => $el[0], $mat));
        $code = preg_replace("/^\s*use\s+[^;]+;\s*$/ms", "", $code);
        return [$code, $use];
    }

    public function split_sfc(DOMDocument $dom, $name, $is_layout = false) {
        $parts = ['php' => "", 'vue' => "", 'css' => "", 'uid' => $name . '-' . uniqid()];
        // dom::d("split $name -- ", $dom);
        if ($this->engine->opts['css'] == 'scoped_simple') {
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
        //    print_r($parts);
        return $parts;
    }
}
