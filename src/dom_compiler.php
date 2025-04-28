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
    private $expressionParser;

    public function __construct(public HTMLDocument $dom, array $methods, public compiler_options $compiler_options, public ?Document $head = null) {
        // $this->expressionParser = new CachingExpressionParser(new BasicJsExpressionParser($methods));
        // $this->expressionParser = new SMPLang(['strrev' => 'strrev']);
        $this->expressionParser = new expressions();
    }

    public function compile() {
        if ($this->dom->doctype) {
            $this->result[] = new instruction("doctype", html: $this->dom->saveHtml($this->dom->doctype));
        }
        $this->walk_nodes($this->dom->documentElement, $this->compiler_options);
        // var_dump($this->result);
        return $this->generate_php_code();
    }

    private function walk_nodes(Element $node, compiler_options $compiler_options, ?Element $parent = null) {
        $name = strtolower($node->nodeName);
        if ($compiler_options->check_attribute($node, "else")) {
            throw new Exception("else without if on $name on line " . $node->getLineNo());
        }
        if ($compiler_options->check_attribute($node, "elseif")) {
            throw new Exception("elseif without if on $name on line " . $node->getLineNo());
        }
        if ($attr = $compiler_options->check_and_remove_attribute($node, "slot")) {
            if (!$parent || !str_contains($parent->nodeName, "."))
                throw new Exception("slotted content must be a first level child of a component. error on line " .
                    $node->getLineNo());
            $this->result[] = new instruction("slotted", $attr);
            $this->walk_nodes($node, $compiler_options, $parent);
            $this->result[] = new instruction("endslotted", $attr);
            return;
        }
        if ($attr = $compiler_options->check_and_remove_attribute($node, "if")) {
            $this->result[] = new instruction("if", $attr);
            $this->walk_nodes($node, $compiler_options, $parent);
            // dbg("+++ if => else?", $name, $node->nextElementSibling->nodeName ?? "");
            if (
                $node->nextElementSibling &&
                ($attr = $compiler_options->check_and_remove_attribute($node->nextElementSibling, "elseif"))
            ) {
                $this->result[] = new instruction("elseif", $attr);
                $this->walk_nodes($node->nextElementSibling, $compiler_options, $parent);
                $this->removeNode($node->nextElementSibling);
            }
            if (
                $node->nextElementSibling &&
                ($compiler_options->check_and_remove_attribute($node->nextElementSibling, "else") !== false)
            ) {
                $this->result[] = new instruction("else");
                $this->walk_nodes($node->nextElementSibling, $compiler_options, $parent);
                $this->removeNode($node->nextElementSibling);
            }
            $this->result[] = new instruction("endif");
            return;
        }
        if ($attr = $compiler_options->check_and_remove_attribute($node, "for")) {
            $for_parts = $this->parse_for_attribute($attr);
            $this->result[] = new instruction("foreach", for_expression: $for_parts);
            $this->walk_nodes($node, $compiler_options, $parent);
            $this->result[] = new instruction("endforeach", for_expression: $for_parts);
            return;
        }

        // TODO: check allowed tags
        if ($attr = $compiler_options->check_and_remove_attribute($node, "html")) {
            $tag = tag::new_from_dom_element($node, $compiler_options->binding_prefixes(), html: $attr);
            $this->result[] = new instruction("tag", tag: $tag);
            // $this->result[] = new instruction("html", $attr);
            // ignore children
            $this->result[] = new instruction("endtag", tag: $tag);
            return;
        }

        $tag = tag::new_from_dom_element($node, $compiler_options->binding_prefixes());
        // dbg("++ path", $node->getNodePath());
        $this->result[] = new instruction("tag", tag: $tag);
        if ($name == "head" && $this->head) {
            $this->walk_nodes($this->head->documentElement, $compiler_options, $node);
        }
        foreach ($node->childNodes as $cnode) {
            if (strtolower($cnode->nodeName) == "#comment") {
                // dbg("++comment");
                $this->result[] = new instruction(strtolower($cnode->nodeName), parent_element: $name, text: $cnode->textContent);
                continue;
            }
            // dbg("+++ is textnode?", $cnode->nodeName);
            if ($this->isTextNode($cnode)) {
                // dbg("++yes");
                $this->result[] = new instruction(strtolower($cnode->nodeName), parent_element: $name, text: $cnode->textContent);
                continue;
            }
            //dbg("++no");
            $this->walk_nodes($cnode, $compiler_options, $node);
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
            str_contains($item_key, "=>") => [$key, $item] = explode('=>', $item_key),
            default => [$item, $key] = [$item_key, ""]
        };
        return [
            "item" => trim($item),
            "key" => trim((string)$key),
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

    /*    
    
    private function isRemovedFromTheDom(Node $node): bool {
        return $node->parentNode === null;
    }

    private function evaluateExpression($expression, array $data, array $methods = []) {
        return $this->expressionParser->evaluate($expression, $data + $methods);
        // return $this->expressionParser->parse($expression, $methods)->evaluate($data);
    }
*/
}
