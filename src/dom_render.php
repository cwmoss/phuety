<?php

namespace phuety;

use DOMAttr;
use DOMCharacterData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use Exception;
use LibXMLError;
use phuety\compiler;
use WMDE\VueJsTemplating\JsParsing\BasicJsExpressionParser;
use WMDE\VueJsTemplating\JsParsing\CachingExpressionParser;
use WMDE\VueJsTemplating\JsParsing\JsExpressionParser;

class dom_render {

    /**
     * @var string HTML
     */
    private $template;

    /**
     * @var JsExpressionParser
     */
    private $expressionParser;

    /**
     * @param string $template HTML
     * @param callable[] $methods
     */
    public function __construct($template, array $methods) {
        $this->template = $template;
        $this->expressionParser = new CachingExpressionParser(new BasicJsExpressionParser($methods));
    }

    public function render_page_dom($dom, array $data, array $methods = []) {
        // $dom->is_page = true;
        $this->handleNode($dom->documentElement, $data, $methods);
        // return $dom;
    }
    /**
     * @param array $data
     *
     * @return string HTML
     */
    public function render_dom($dom, array $data, array $methods = []) {
        #var_dump("render-dom");
        #var_dump($data);
        #var_dump($methods);
        $this->handleNode($dom->documentElement, $data, $methods);
        return;
    }


    /**
     * @param DOMNode $node
     * @param array $data
     */
    private function handleNode(DOMNode $node, array $data, array $methods = []) {
        $this->replaceMustacheVariables($node, $data, $methods);

        if (!$this->isTextNode($node)) {
            // $this->stripEventHandlers($node);
            $this->handleIf($node->childNodes, $data, $methods);
            $this->handleFor($node, $data, $methods);
            $this->handleRawHtml($node, $data, $methods);

            if (!$this->isRemovedFromTheDom($node)) {
                $this->handleAttributeBinding($node, $data, $methods);

                foreach (iterator_to_array($node->childNodes) as $childNode) {
                    $this->handleNode($childNode, $data, $methods);
                }
            }
        }
    }


    /**
     * @param DOMNode $node
     * @param array $data
     */
    private function replaceMustacheVariables(DOMNode $node, array $data, array $methods = []) {
        // print_r($methods);
        if ($node instanceof DOMText) {
            $text = $node->wholeText;

            $regex = '/\{\{(?P<expression>.*?)\}\}/x';
            preg_match_all($regex, $text, $matches);

            foreach ($matches['expression'] as $index => $expression) {
                $value = $this->expressionParser->parse($expression, $methods)
                    ->evaluate($data, $methods);

                $text = str_replace($matches[0][$index], $value, $text);
            }

            if ($text !== $node->wholeText) {
                $newNode = $node->ownerDocument->createTextNode($text);
                $node->parentNode->replaceChild($newNode, $node);
            }
        }
    }

    private function handleAttributeBinding(DOMElement $node, array $data, array $methods = []) {
        /** @var DOMAttr $attribute */
        foreach (iterator_to_array($node->attributes) as $attribute) {
            if (!preg_match('/^:[\w-]+$/', $attribute->name)) {
                continue;
            }

            $value = $this->expressionParser->parse($attribute->value, $methods)
                ->evaluate($data);

            $name = substr($attribute->name, 1);
            if (is_bool($value)) {
                if ($value) {
                    $node->setAttribute($name, $name);
                }
            } else {
                $node->setAttribute($name, $value);
            }
            $node->removeAttribute($attribute->name);
        }
    }

    /**
     * @param DOMNodeList $nodes
     * @param array $data
     */
    private function handleIf(DOMNodeList $nodes, array $data, array $methods = []) {
        // Iteration of iterator breaks if we try to remove items while iterating, so defer node
        // removing until finished iterating.
        $nodesToRemove = [];
        $nodesToReplace = [];

        foreach ($nodes as $node) {
            if ($this->isTextNode($node)) {
                continue;
            }
            if ($node->nodeType == 7) continue;
            /** @var DOMElement $node */
            if ($node->hasAttribute('v-if')) {
                $conditionString = $node->getAttribute('v-if');
                $node->removeAttribute('v-if');
                $condition = $this->evaluateExpression($conditionString, $data, $methods);

                if (!$condition) {
                    $nodesToRemove[] = $node;
                } else {
                    if ($node->tagName == 'template') {
                        $nodesToReplace[] = $node;
                    }
                }

                $previousIfCondition = $condition;
            } elseif ($node->hasAttribute('v-else')) {
                $node->removeAttribute('v-else');

                if ($previousIfCondition) {
                    $nodesToRemove[] = $node;
                } else {
                    if ($node->tagName == 'template') {
                        $nodesToReplace[] = $node;
                    }
                }
            }
        }

        foreach ($nodesToRemove as $node) {
            $this->removeNode($node);
        }
        foreach ($nodesToReplace as $node) {
            $this->replace_with_childs($node);
        }
    }

    private function handleFor(DOMNode $node, array $data, array $methods = []) {
        if ($this->isTextNode($node)) {
            return;
        }

        /** @var DOMElement $node */
        if ($node->hasAttribute('v-for')) {
            list($itemName, $listName) = explode(' in ', $node->getAttribute('v-for'));
            $node->removeAttribute('v-for');
            $value = $this->expressionParser->parse($listName, $methods)
                ->evaluate($data);
            foreach ($value as $item) {
                $newNode = $node->cloneNode(true);
                $node->parentNode->insertBefore($newNode, $node);
                $this->handleNode($newNode, array_merge($data, [$itemName => $item]), $methods);
                if ($node->tagName == 'template') {
                    dom::d('for-template', $node);
                    $this->replace_with_childs($newNode);
                }
            }

            $this->removeNode($node);
        }
    }



    private function handleRawHtml(DOMNode $node, array $data, array $methods = []) {
        if ($this->isTextNode($node)) {
            return;
        }

        /** @var DOMElement $node */
        if ($node->hasAttribute('v-html')) {
            $variableName = $node->getAttribute('v-html');
            $node->removeAttribute('v-html');

            $newNode = $node->cloneNode(true);

            dom::append_html($newNode, $data[$variableName]);

            $node->parentNode->replaceChild($newNode, $node);
        }
    }

    /**
     * @param string $expression
     * @param array $data
     *
     * @return bool
     */
    private function evaluateExpression($expression, array $data, array $methods = []) {
        return $this->expressionParser->parse($expression, $methods)->evaluate($data);
    }

    private function removeNode(DOMElement $node) {
        $node->parentNode->removeChild($node);
    }

    public function replace_with_childs(DOMElement $node) {
        $node->replaceWith(...$node->childNodes);
    }
    /**
     * @param DOMNode $node
     *
     * @return bool
     */
    private function isTextNode(DOMNode $node) {
        return $node instanceof DOMCharacterData;
    }

    private function isRemovedFromTheDom(DOMNode $node) {
        return $node->parentNode === null;
    }
}
