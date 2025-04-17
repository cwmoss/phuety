<?php

namespace phuety;

use Dom\Element;
use Dom\Node;
use Dom\NodeList;
use Dom\CharacterData;
use DOM\Attr;
use Dom\Text;
use Dom\Document;
use Dom\HTMLDocument;
use Exception;
use Le\SMPLang\SMPLang;

/*
TODO: 
    - named slots
    - bindings, boolean
    - classmap
    - stylemap
*/

class dom_compiler {

    /**
     * @var string HTML
     */
    private $template;

    public array $result = [];
    public $lang_attrs = [
        'if' => 'if',
        'else' => 'else',
        'for' => 'foreach',
        'html' => 'html',
        'bind' => 'bind'
    ];
    public $lang_shortcut = ":";
    public $lang_prefix = "v-";
    public array $lang_bindings_prefixes = [];
    private $expressionParser;

    public function __construct(public HTMLDocument $dom, array $methods, public ?Document $head = null) {
        // $this->expressionParser = new CachingExpressionParser(new BasicJsExpressionParser($methods));
        // $this->expressionParser = new SMPLang(['strrev' => 'strrev']);
        $this->expressionParser = new expressions();
        $this->lang_bindings_prefixes = [
            $this->lang_prefix . $this->lang_attrs["bind"] . $this->lang_shortcut,
            $this->lang_shortcut,
        ];
    }

    public function compile() {
        if ($this->dom->doctype) {
            $this->result[] = new instruction("doctype", html: $this->dom->saveHtml($this->dom->doctype));
        }
        $this->walk_nodes($this->dom->documentElement);
        // var_dump($this->result);
        return $this->generate_php_code();
    }

    private function walk_nodes(Element $node, $block = "") {
        $name = strtolower($node->nodeName);
        if ($this->check_attribute($node, "else")) {
            throw new Exception("else without if on $name on line " . $node->getLineNo());
        }
        if ($attr = $this->check_and_remove_attribute($node, "if")) {
            $this->result[] = new instruction("if", $attr);
            $this->walk_nodes($node, "if");

            if (
                $node->nextElementSibling &&
                ($attr = $this->check_and_remove_attribute($node->nextElementSibling, "else"))
            ) {
                $this->result[] = new instruction("else", $attr);
                $this->walk_nodes($node->nextElementSibling, "else");
            }
            $this->result[] = new instruction("endif");
            return;
        }
        if ($attr = $this->check_and_remove_attribute($node, "for")) {
            $for_parts = $this->parse_for_attribute($attr);
            $this->result[] = new instruction("foreach", for_expression: $for_parts);
            $this->walk_nodes($node, "foreach");
            $this->result[] = new instruction("endforeach", for_expression: $for_parts);
            return;
        }

        // TODO: check allowed tags
        if ($attr = $this->check_and_remove_attribute($node, "html")) {
            $tag = tag::new_from_dom_element($node, $this->lang_bindings_prefixes);
            $this->result[] = new instruction("tag", tag: $tag);
            $this->result[] = new instruction("html", html: $attr);
            // ignore children
            $this->result[] = new instruction("endtag", tag: $tag);
        }

        $tag = tag::new_from_dom_element($node, $this->lang_bindings_prefixes);
        // dbg("++ path", $node->getNodePath());
        $this->result[] = new instruction("tag", tag: $tag);
        if ($name == "head" && $this->head) {
            $this->walk_nodes($this->head->documentElement);
        }
        foreach ($node->childNodes as $cnode) {
            // dbg("+++ is textnode?", $cnode->nodeName);
            if ($this->isTextNode($cnode)) {
                // dbg("++yes");
                $this->result[] = new instruction(strtolower($cnode->nodeName), parent_element: $name, text: $cnode->textContent);
                continue;
            }
            //dbg("++no");
            $this->walk_nodes($cnode);
        }

        $this->result[] = new instruction("endtag", tag: $tag);
    }

    private function parse_for_attribute($attr) {
        match (true) {
            str_contains($attr, " in ") => [$item_key, $list] = explode(' in ', $attr),
            str_contains($attr, " as ") => [$list, $item_key] = explode(' as ', $attr),
            default => throw new Exception("could not resolve for expression ($attr)", 1)
        };
        match (true) {
            str_contains($item_key, ",") => [$item, $key] = explode(',', $item_key),
            str_contains($item_key, "=>") => [$list, $item_key] = explode(' as ', $item_key),
            default => $item = $item_key
        };
        return [
            "item" => trim($item),
            "key" => trim($key),
            "list" => trim($list)
        ];
    }

    private function generate_php_code(): string {
        $code = [];
        foreach ($this->result as $instruction) {
            $code[] = $instruction->compile($this->expressionParser);
        }
        return join("", $code);
    }

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

    private function isTextNode(Node $node): bool {
        // return $node->nodeType == \XML_TEXT_NODE;
        return $node instanceof CharacterData;
    }

    private function check_attribute(Element $node, string $attr): bool|string {
        $attr_l = $this->lang_prefix . $this->lang_attrs[$attr];
        $attr_s = $this->lang_shortcut . $this->lang_attrs[$attr];
        return match (true) {
            $node->hasAttribute($attr_l) => $attr_l,
            $node->hasAttribute($attr_s) => $attr_s,
            default => false
        };
    }
    private function check_and_remove_attribute(Element $node, string $attr): bool|string {
        $found = $this->check_attribute($node, $attr);
        if (!$found) return false;
        // dbg("attr", $found);
        $attribute = $node->getAttribute($found);
        $node->removeAttribute($found);
        return $attribute;
    }

    private function isRemovedFromTheDom(Node $node): bool {
        return $node->parentNode === null;
    }
}
