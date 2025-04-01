<?php

namespace phuety;

use Dom\Element;
use Dom\Node;
use Dom\NodeList;
use Dom\CharacterData;
use DOM\Attr;
use Dom\Text;
use Dom\Document;

use Le\SMPLang\SMPLang;


class dom_compiler {

    /**
     * @var string HTML
     */
    private $template;

    public array $result = [];
    public $lang_attrs = [
        'if' => 'v-if',
        'else' => 'v-else',
        'for' => 'v-for',
        'html' => 'v-html',
        'bind' => ':'
    ];

    private SMPLang $expressionParser;

    /**
     * @param string $template HTML
     * @param callable[] $methods
     */
    public function __construct(public Document $dom, array $methods) {
        // $this->expressionParser = new CachingExpressionParser(new BasicJsExpressionParser($methods));
        $this->expressionParser = new SMPLang(['strrev' => 'strrev']);
    }

    public function render_page_dom(Document $dom, props $props, array $data, array $methods = []) {
        // $dom->is_page = true;
        $this->handleNode($dom->documentElement, $data, $methods, $props);
        // return $dom;
    }
    /**
     * @param array $data
     *
     * @return string HTML
     */
    public function compile() {
        $this->handleNode($this->dom->documentElement);
        return $this->result;
    }


    /**
     * @param DOMNode $node
     * @param array $data
     */
    private function handleNode(Node $node) {
        $this->result[] = $this->replaceMustacheVariables($node);

        if (!$this->isTextNode($node)) {
            // $this->stripEventHandlers($node);
            $this->handleIf($node->childNodes);
            # $for = $this->handleFor($node, $data, $methods, $props);

            # if ($for !== true) {
            #     $this->handleAttributeBinding($node, $data, $methods,  $props);
            #    $this->handleRawHtml($node, $data, $methods, $props);
            # }
            if (!$this->isRemovedFromTheDom($node)) {

                foreach (iterator_to_array($node->childNodes) as $childNode) {
                    $this->handleNode($childNode);
                }
            }
        }
    }


    /**
     * @param DOMNode $node
     * @param array $data
     */
    private function replaceMustacheVariables(Element|Text $node) {
        // print_r($methods);
        if ($node instanceof Text) {
            $text = $node->wholeText;

            $regex = '/\{\{(?P<expression>.*?)\}\}/x';
            preg_match_all($regex, $text, $matches);

            foreach ($matches['expression'] as $index => $expression) {
                //$value = $this->expressionParser->parse($expression, $methods)
                //    ->evaluate($data, $methods);
                // $value = $this->expressionParser->evaluate($expression, $data + $methods);
                $value = $this->php_replace_mustache($expression);
                $text = str_replace($matches[0][$index], $value, $text);
            }
            return $text;

            if ($text !== $node->wholeText) {
                $newNode = $node->ownerDocument->createTextNode($text);
                $node->parentNode->replaceChild($newNode, $node);
            }
        }
    }

    function php_replace_mustache($expression) {
        return sprintf('<?= $__expr->evaluate("%s", $__data) ?>', $expression);
    }

    private function handleAttributeBinding(Element $node, array $data, array $methods, props $props) {
        /** @var Attr $attribute */

        // TODO: is_component?
        $uid = 'userdata' . uniqid();
        $attributes = dom::attributes($node);
        $bind = $this->lang_attrs['bind'];
        foreach (iterator_to_array($node->attributes) as $attribute) {
            if (!preg_match('/^' . $bind . '[\w-]+$/', $attribute->name)) {
                continue;
            }

            // $value = $this->expressionParser->parse($attribute->value, $methods)
            //    ->evaluate($data);
            // print_r($data);
            $value = $this->expressionParser->evaluate($attribute->value, $data + $methods);
            // 
            $name = substr($attribute->name, strlen($bind));
            //            print "attr {$attribute->name} => $name \n";
            if ($name == 'class') {
                $class = $node->getAttribute('class');
                if (!is_string($value)) $value = (array) $value;
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (is_numeric($k)) {
                            $class .= " $v";
                        } else {
                            if ($v) {
                                $class .= " $k";
                            }
                        }
                    }
                } else {
                    $class .= " $value";
                }
                $class = trim($class);
                $node->setAttribute('class', $class);
            } else {
                if (is_bool($value)) {
                    if ($value) {
                        $node->setAttribute($name, $name);
                    }
                } else {
                    // postbone resolve till later (happens in components)
                    if (is_array($value) || is_object($value)) {

                        $props->set($uid, $name, $value);
                        //    $data[$uid] = $value;
                        //    $node->setAttribute(':' . $name, $uid);
                        // $node->data[$name] = $value;
                        // print_r($name);
                        // print_r($node->data);
                        $node->setAttribute('props', $uid);
                    } else {
                        $node->setAttribute($name, $value);
                    }
                }
            }
            $node->removeAttribute($attribute->name);
        }
    }

    /**
     * @param DOMNodeList $nodes
     * @param array $data
     */
    private function handleIf(NodeList $nodes) {
        // Iteration of iterator breaks if we try to remove items while iterating, so defer node
        // removing until finished iterating.
        $if = $this->lang_attrs['if'];
        $else = $this->lang_attrs['else'];

        $nodesToRemove = [];
        $nodesToReplace = [];
        $previousIfCondition = null;
        foreach ($nodes as $node) {
            if ($this->isTextNode($node)) {
                continue;
            }
            if ($node->nodeType == \XML_PI_NODE) continue;
            /** @var Element $node */
            if ($node->hasAttribute($if)) {
                $conditionString = $node->getAttribute($if);
                $node->removeAttribute($if);
                $condition = $this->evaluateExpression($conditionString, $data, $methods);

                if (!$condition) {
                    $nodesToRemove[] = $node;
                } else {
                    if ($node->tagName == 'template') {
                        $nodesToReplace[] = $node;
                    }
                }

                $previousIfCondition = $condition;
            } elseif ($node->hasAttribute($else)) {
                $node->removeAttribute($else);

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

    private function handleFor(Element $node, array $data, array $methods, props $props) {
        if ($this->isTextNode($node)) {
            return;
        }
        $for = $this->lang_attrs['for'];
        /** @var Element $node */
        if ($node->hasAttribute($for)) {
            list($itemName, $listName) = explode(' in ', $node->getAttribute($for));
            $node->removeAttribute($for);
            // $value = $this->expressionParser->parse($listName, $methods)
            //    ->evaluate($data);
            $value = $this->expressionParser->evaluate($listName, $data + $methods);
            foreach ($value as $item) {
                $newNode = $node->cloneNode(true);
                $node->parentNode->insertBefore($newNode, $node);
                //print "FOR nav";
                //var_dump([$itemName => $item]);
                $this->handleNode($newNode, array_merge($data, [$itemName => $item]), $methods, $props);
                if ($node->tagName == 'template') {
                    dom::d('for-template', $node);
                    $this->replace_with_childs($newNode);
                }
            }

            $this->removeNode($node);
            return true;
        }
    }


    private function handleRawHtml(Element $node, array $data, array $methods, props $props) {
        if ($this->isTextNode($node)) {
            return;
        }
        $html = $this->lang_attrs['html'];

        /** @var Element $node */
        if ($node->hasAttribute($html)) {
            $variableName = $node->getAttribute($html);
            $value = $this->expressionParser->evaluate($variableName, $data + $methods);
            $node->removeAttribute($html);

            $newNode = $node->cloneNode(true);
            #dom::d("v-html", $newNode);
            dom::append_html($newNode, $value);

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
        return $this->expressionParser->evaluate($expression, $data + $methods);
        // return $this->expressionParser->parse($expression, $methods)->evaluate($data);
    }

    private function removeNode(Element $node) {
        $node->parentNode->removeChild($node);
    }

    public function replace_with_childs(Element $node) {
        $node->replaceWith(...$node->childNodes);
    }
    /**
     * @param DOMNode $node
     *
     * @return bool
     */
    private function isTextNode(Node $node) {
        // return $node->nodeType == \XML_TEXT_NODE;
        return $node instanceof CharacterData;
    }

    private function isRemovedFromTheDom(Node $node) {
        return $node->parentNode === null;
    }
}
