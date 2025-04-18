<?php

namespace phuety;

class instruction {

    public function __construct(
        public string $name,
        public ?string $expression = null,
        public ?tag $tag = null,
        public ?array $for_expression = null,
        public ?string $html = null,
        public ?string $text = null,
        public ?string $parent_element = null
    ) {
    }

    public function compile($ep): string {

        $php = match ($this->name) {
            "if" => sprintf('<?php if(%s){ ?>', $this->compile_expression($this->expression, $ep)),
            "foreach" => $this->php_foreach($ep),
            "#text" => $this->parent_element == "script" ? $this->text : $this->php_replace_mustache($this->text, $ep),
            "html" => sprintf('<?= %s ?>', $this->compile_expression($this->expression, $ep)),
            "endif" => '<?php } ?>',
            "endforeach" => $this->php_foreach_end(),
            "else" => '<?php } else { ?>',
            "tag" => $this->php_element($ep),
            "endtag" => $this->php_element_end($ep),
            "doctype" => $this->html,
            "#comment" => "",
            default => "default-{$this->name}"
        };

        return $php;
    }

    function php_element($ep): string {
        $tag = $this->tag;
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
        // if ($tag->tagname == "xead") $tag->tagname = "head";
        if (!$tag->bindings) return $tag->open();
        return sprintf(
            '<?= tag::tag_open_merged_attrs("%s", %s, %s) ?>',
            $tag->tagname,
            $this->php_bindings($ep),
            var_export($tag->attrs, true)
        );
    }

    function php_element_end($ep): string {
        $tag = $this->tag;
        if ($tag->tagname == "template.") return "";
        if ($tag->is_slot) return "";
        // if ($tag->tagname == "xead") $tag->tagname = "head";
        // TODO: empty slots
        if ($tag->is_component) {
            return sprintf(
                '<?=$this->engine->get_component("%s")->run(%s + %s %s); ?>',
                $tag->tagname,
                $this->php_bindings($ep),
                var_export($tag->attrs, true),
                $tag->has_children ? ', ["default" => ob_get_clean()]' : '',
            );
        }
        return tag::tag_close($tag->tagname); // sprintf('</%s>', $tag->tagname);
    }

    function php_bindings($ep) {
        $php = [];
        foreach ($this->tag->bindings as $name => $expression) {
            $php[] = sprintf('"%s"=> %s', $name, $this->compile_expression($expression, $ep));
            // $this->ep->evaluate("%s", $__blockdata + $__data)
        }
        return '[' . join(", ", $php) . ']';
    }

    function php_foreach($ep): string {
        dbg("++foreach++", $this);
        $item = trim($this->for_expression["item"]);
        $key = trim($this->for_expression["key"]);
        $list = trim($this->for_expression["list"]);
        return sprintf(
            '<?php foreach(%s as %s $%s){$__d->_add_block(["%s"=>$%s %s]); ?>',
            $this->compile_expression($list, $ep),
            $key ? sprintf('$%s => ', $key) : '',
            $item,
            $item,
            $item,
            $key ? sprintf(', "%s" => $%s', $key, $key) : '',
        );
    }

    function php_foreach_end(): string {
        return sprintf(
            '<?php $__d->_remove_block();} ?>'
            //,
            //$item,
            //$key !== null ? '' : sprintf(', $__blockdata["%s"]', $key)
        );
    }

    function php_replace_mustache($text, $ep): string {
        $regex = '/\{\{(?P<expression>.*?)\}\}/x';
        preg_match_all($regex, $text, $matches);

        foreach ($matches['expression'] as $index => $expression) {
            $value = sprintf('<?= tag::h(%s) ?>', $this->compile_expression($expression, $ep));
            $text = str_replace($matches[0][$index], $value, $text);
        }
        return $text;
    }

    private function compile_expression($expression, $ep) {
        // $this->ep->evaluate("%s", $__blockdata + $__data)
        return $ep->for_phuety($expression);
    }
}
