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
use Le\SMPLang\SMPLang;
use phuety\symfony_el\expressions;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/*
TODO: 
    - named slots
    - bindings, boolean
    - classmap
    - stylemap
*/

class template_compiler {

    /**
     * @var string HTML
     */
    private $template;

    public array $result = [];
    public array $components = [];

    private $expressionParser;

    public function __construct(public HTMLDocument $dom, array $methods, public compiler_options $compiler_options, public ?Document $head = null, public int $total_rootelements = 0) {
        // $this->expressionParser = new CachingExpressionParser(new BasicJsExpressionParser($methods));
        // $this->expressionParser = new SMPLang(['strrev' => 'strrev']);
        $this->expressionParser = new expressions();
    }

    public function compile() {
        if ($this->dom->doctype) {
            $this->result[] = new instruction(0, "doctype", html: $this->dom->saveHtml($this->dom->doctype));
        }

        $this->walk_nodes($this->dom->documentElement, $this->compiler_options);
        // var_dump($this->result);
        return $this->generate_php_code();
    }

    private function walk_nodes(Element $node, compiler_options $compiler_options, ?Element $parent = null, $level = 0) {
        $name = strtolower($node->nodeName);
        $line = $node->getLineNo();

        if ($compiler_options->check_attribute($node, "else")) {
            throw new exception("`:else` without `:if` on <$name>", $line);
        }
        if ($compiler_options->check_attribute($node, "elseif")) {
            throw new exception("`:elseif` without `:if` on <$name>", $line);
        }
        if ($attr = $compiler_options->check_and_remove_attribute($node, "slot")) {
            if (!$parent || !str_contains($parent->nodeName, "."))
                throw new exception(
                    "slotted content <$name> must be a first level child of a component.",
                    $line
                );
            $this->result[] = new instruction($line, "slotted", $attr);
            $this->walk_nodes($node, $compiler_options, $parent, $level);
            $this->result[] = new instruction($line, "endslotted", $attr);
            return;
        }
        if ($attr = $compiler_options->check_and_remove_attribute($node, "if")) {
            $this->result[] = new instruction($line, "if", $attr);
            $this->walk_nodes($node, $compiler_options, $parent, $level);
            // dbg("+++ if => else?", $name, $node->nextElementSibling->nodeName ?? "");
            if (
                $node->nextElementSibling &&
                ($attr = $compiler_options->check_and_remove_attribute($node->nextElementSibling, "elseif"))
            ) {
                $this->result[] = new instruction($node->nextElementSibling->getLineNo(), "elseif", $attr);
                $this->walk_nodes($node->nextElementSibling, $compiler_options, $parent, $level);
                $this->removeNode($node->nextElementSibling);
            }
            if (
                $node->nextElementSibling &&
                ($compiler_options->check_and_remove_attribute($node->nextElementSibling, "else") !== false)
            ) {
                $this->result[] = new instruction($node->nextElementSibling->getLineNo(), "else");
                $this->walk_nodes($node->nextElementSibling, $compiler_options, $parent, $level);
                $this->removeNode($node->nextElementSibling);
            }
            $this->result[] = new instruction($line, "endif");
            return;
        }
        if ($attr = $compiler_options->check_and_remove_attribute($node, "for")) {
            $for_parts = $this->parse_for_attribute($attr, $line);
            $this->result[] = new instruction($line, "foreach", for_expression: $for_parts);
            $this->walk_nodes($node, $compiler_options, $parent, $level);
            $this->result[] = new instruction($line, "endforeach", for_expression: $for_parts);
            return;
        }

        if ($attr = $compiler_options->check_and_remove_attribute($node, "html")) {
            if (in_array($name, tag::$self_closing_tags)) {
                throw new exception("the <$name> element cannot have `:html` contents", $line);
            }
            $tag = tag::new_from_dom_element($node, $compiler_options->binding_prefixes(), html: $attr);
            $this->result[] = new instruction($line, "tag", tag: $tag, level: $level, single_root: ($this->total_rootelements == 1));
            // $this->result[] = new instruction("html", $attr);
            // ignore children
            $this->result[] = new instruction($line, "endtag", tag: $tag);
            if ($tag->is_component) $this->components[] = $name;
            return;
        }

        $tag = tag::new_from_dom_element($node, $compiler_options->binding_prefixes());
        if ($tag->is_component) $this->components[] = $name;

        // dbg("++ path", $node->getNodePath());
        $this->result[] = new instruction($line, "tag", tag: $tag, level: $level, single_root: ($this->total_rootelements == 1));
        if ($name == "head" && $this->head) {
            $this->walk_nodes($this->head->documentElement, $compiler_options, $node, $level);
        }
        foreach ($node->childNodes as $cnode) {
            if (strtolower($cnode->nodeName) == "#comment") {
                // dbg("++comment");
                $this->result[] = new instruction($cnode->getLineNo(), strtolower($cnode->nodeName), parent_element: $name, text: $cnode->textContent);
                continue;
            }
            // dbg("+++ is textnode?", $cnode->nodeName);
            if ($this->isTextNode($cnode)) {
                // dbg("textnode", $cnode->getLineNo(), $cnode->textContent);
                $this->result[] = new instruction($cnode->getLineNo(), strtolower($cnode->nodeName), parent_element: $name, text: $cnode->textContent);
                continue;
            }
            //dbg("++no");
            $this->walk_nodes($cnode, $compiler_options, $node, $level + 1);
        }

        $this->result[] = new instruction($line, "endtag", tag: $tag);
    }

    private function parse_for_attribute($attr, $line) {
        match (true) {
            str_contains($attr, " in ") => [$item_key, $list] = explode(' in ', $attr),
            str_contains($attr, " as ") => [$list, $item_key] = explode(' as ', $attr),
            default => throw new exception("could not resolve `:foreach` expression ($attr)", $line)
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
        try {
            foreach ($this->result as $instruction) {
                $code[] = $instruction->compile($this->expressionParser);
            }
        } catch (SyntaxError $e) {
            throw exception::new_from_expressionparser($e, $instruction);
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
