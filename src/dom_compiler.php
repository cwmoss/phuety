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
    - bindings
    - classmap
    - stylemap
    - p.template
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
            $this->result[] = ["doctype", $this->dom->saveHtml($this->dom->doctype)];
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
            $this->result[] = ["if", $attr];
            $this->walk_nodes($node, "if");

            if (
                $node->nextElementSibling &&
                ($attr = $this->check_and_remove_attribute($node->nextElementSibling, "else"))
            ) {
                $this->result[] = ["else", $attr];
                $this->walk_nodes($node->nextElementSibling, "else");
            }
            $this->result[] = ["endif"];
            return;
        }
        if ($attr = $this->check_and_remove_attribute($node, "for")) {
            $for_parts = $this->parse_for_attribute($attr);
            $this->result[] = ["foreach", $for_parts];
            $this->walk_nodes($node, "foreach");
            $this->result[] = ["endforeach", $for_parts];
            return;
        }

        // TODO: check allowed tags
        if ($attr = $this->check_and_remove_attribute($node, "html")) {
            $tag = tag::new_from_dom_element($node, $this->lang_bindings_prefixes);
            $this->result[] = ["tag", $tag];
            $this->result[] = ["html", $attr];
            // ignore children
            $this->result[] = ["endtag", $tag];
            return;
        }

        $tag = tag::new_from_dom_element($node, $this->lang_bindings_prefixes);
        // dbg("++ path", $node->getNodePath());
        $this->result[] = ["tag", $tag];
        if ($name == "head" && $this->head) {
            $this->walk_nodes($this->head->documentElement);
        }
        foreach ($node->childNodes as $cnode) {
            // dbg("+++ is textnode?", $cnode->nodeName);
            if ($this->isTextNode($cnode)) {
                // dbg("++yes");
                $this->result[] = [strtolower($cnode->nodeName), $name, $cnode->textContent];
                continue;
            }
            //dbg("++no");
            $this->walk_nodes($cnode);
        }

        $this->result[] = ["endtag", $tag];
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
        foreach ($this->result as $stack_code) {
            $c = $stack_code[0];
            $php = match (true) {
                $c == "if" => sprintf('<?php if(%s){ ?>', $this->compile_expression($stack_code[1])),
                $c == "foreach" => $this->php_foreach($stack_code[1]),
                $c == "#text" => $stack_code[1] == "script" ? $stack_code[2] : $this->php_replace_mustache($stack_code[2]),
                $c == "html" => sprintf('<?= %s ?>', $this->compile_expression($stack_code[1])),
                $c == "endif" => '<?php } ?>',
                $c == "endforeach" => $this->php_foreach($stack_code[1], true),
                $c == "else" => '<?php } else { ?>',
                $c == "tag" => $this->php_element($stack_code[1]),
                $c == "endtag" => $this->php_element_end($stack_code[1]),
                $c == "doctype" => $stack_code[1],
                $c == "#comment" => "",
                default => "default-$c"
            };
            $code[] = $php;
        }
        return join("", $code);
    }

    function php_element(tag $tag): string {
        if ($tag->tagname == "template.") return "";
        if ($tag->is_slot) {
            return sprintf('<?=$slots["default"]?>');
        }
        if ($tag->is_component) {
            return $tag->has_children ? sprintf('<?php ob_start(); ?>') : '';
        }
        // if ($tag->is_asset) {
        //     return sprintf('< ?=$this->assetholder->get("%s")? >', $tag->attrs["position"]);
        // }
        if ($tag->tagname == "xead") $tag->tagname = "head";
        if (!$tag->bindings) return $tag->open();
        return sprintf(
            '<?= tag::tag_open_merged_attrs("%s", %s, %s) ?>',
            $tag->tagname,
            $this->php_bindings($tag),
            var_export($tag->attrs, true)
        );
    }

    function php_element_end(tag $tag): string {
        if ($tag->tagname == "template.") return "";
        if ($tag->is_slot) return "";
        if ($tag->tagname == "xead") $tag->tagname = "head";
        // TODO: empty slots
        if ($tag->is_component) {
            return sprintf(
                '<?=$this->engine->get_component("%s")->run(%s + %s %s); ?>',
                $tag->tagname,
                $this->php_bindings($tag),
                var_export($tag->attrs, true),
                $tag->has_children ? ', ["default" => ob_get_clean()]' : '',
            );
        }
        return tag::tag_close($tag->tagname); // sprintf('</%s>', $tag->tagname);
    }

    function php_bindings(tag $tag) {
        $php = [];
        foreach ($tag->bindings as $name => $expression) {
            $php[] = sprintf('"%s"=> %s', $name, $this->compile_expression($expression));
            // $this->ep->evaluate("%s", $__blockdata + $__data)
        }
        return '[' . join(", ", $php) . ']';
    }

    function php_foreach($parts, $end = false): string {
        dbg("++foreach++", $parts);
        $item = trim($parts["item"]);
        $key = trim($parts["key"]);
        $list = trim($parts["list"]);
        if ($end) {
            // endforeach
            return sprintf(
                '<?php $__d->remove_block();} ?>'
                //,
                //$item,
                //$key !== null ? '' : sprintf(', $__blockdata["%s"]', $key)
            );
        }
        return sprintf(
            '<?php foreach(%s as %s $%s){$__d->add_block(["%s"=>$%s %s]); ?>',
            $this->compile_expression($list),
            $key ? sprintf('$%s => ', $key) : '',
            $item,
            $item,
            $item,
            $key ? sprintf(', "%s" => $%s', $key, $key) : '',
        );
    }

    function php_replace_mustache($text): string {
        $regex = '/\{\{(?P<expression>.*?)\}\}/x';
        preg_match_all($regex, $text, $matches);

        foreach ($matches['expression'] as $index => $expression) {
            $value = sprintf('<?= tag::h(%s) ?>', $this->compile_expression($expression));
            $text = str_replace($matches[0][$index], $value, $text);
        }
        return $text;
    }

    private function compile_expression($expression) {
        // $this->ep->evaluate("%s", $__blockdata + $__data)
        return $this->expressionParser->for_phuety($expression);
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
