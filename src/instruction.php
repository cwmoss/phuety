<?php

namespace phuety;

class instruction {

    public function __construct(
        public int $line_no,
        public string $name,
        public ?string $expression = null,
        public ?tag $tag = null,
        public ?array $for_expression = null,
        public ?string $html = null,
        public ?string $text = null,
        public ?string $parent_element = null,
        public ?int $level = null,
        public bool $single_root = false
    ) {
    }

    public function compile($ep): string {

        $php = match ($this->name) {
            "if" => sprintf('<?php if(%s){ ?>', $this->compile_expression($this->expression, $ep)),
            "elseif" => sprintf('<?php }elseif(%s){ ?>', $this->compile_expression($this->expression, $ep)),
            "foreach" => $this->php_foreach($ep),
            "#text" => $this->parent_element == "script" ? $this->text : $this->php_replace_mustache($this->text, $ep),
            /* 
            "html" => sprintf('<?= %s ?>', $this->compile_expression($this->expression, $ep)),
            */
            "endif" => '<?php } ?>',
            "endforeach" => $this->php_foreach_end(),
            "else" => '<?php } else { ?>',
            "tag" => $this->php_element($ep),
            "endtag" => $this->php_element_end($ep),
            "slotted" => '<?php ob_start(); ?>',
            "endslotted" => sprintf('<?php $__s[0]["%s"]=ob_get_clean(); ?>', $this->expression),
            "doctype" => $this->html,
            "#comment" => '<!--' . $this->text . '-->',
            "#skip" => $this->mask_phptags($this->text),
            default => "default-{$this->name}"
        };

        return $php;
    }

    public function mask_phptags($text) {
        return str_replace(['<?php', '?>'], ['<?="<?php"?>', '<?="?>"'], $text);
    }

    public function php_element($ep): string {
        $tag = $this->tag;
        $html = "";
        if ($tag->html_content_expression) {
            $html = sprintf('<?= %s ?>', $this->compile_expression($tag->html_content_expression, $ep));
        }

        if ($tag->tagname == "template.") {
            return $html;
        }
        if ($tag->is_slot) {
            $slotcontent = sprintf('<?=$slots["%s"]??""?>', $tag->slotname);
            if ($tag->has_children) {
                $slotcontent .= sprintf('<?php if(!($slots["%s"]??"")){ ?>', $tag->slotname);
            }
            return $slotcontent;
        }
        if ($tag->is_component) {
            if ($tag->has_children || $html) return sprintf('<?php array_unshift($__s, []); ob_start(); ?>') . $html;
            return "";
        }
        // if ($tag->is_asset) {
        //     return sprintf('< ?=$this->assetholder->get("%s")? >', $tag->attrs["position"]);
        // }
        // if ($tag->tagname == "xead") $tag->tagname = "head";
        $is_fallthrough = ($this->level == 1 && $this->single_root);
        if (!$tag->bindings && !$is_fallthrough) return $tag->open() . $html;
        return sprintf(
            '<?= tag::tag_open_merged_attrs("%s", %s, %s %s) ?>',
            $tag->tagname,
            $this->php_bindings($ep),
            var_export($tag->attrs, true),
            $is_fallthrough ? ', $__d->_get("props")' : ''
        ) . $html;
    }

    function php_element_end($ep): string {
        $tag = $this->tag;
        if ($tag->tagname == "template.") {
            return "";
        }
        if ($tag->is_slot) {
            if ($tag->has_children) return '<?php } ?>';
            return "";
        }
        // if ($tag->tagname == "xead") $tag->tagname = "head";
        // 
        // $__engine->get_component("%s")->run($__engine, %s + %s %s);
        if ($tag->is_component) {
            return sprintf(
                '<?php $__runner($__runner, "%s", %s, %s + %s %s); ?>',
                $tag->tagname,
                sprintf('$__d->_get("phuety")->with($this->tagname, "%s")', $tag->tagname),
                $this->php_bindings($ep, true),
                var_export($tag->attrs, true),
                ($tag->has_children || $tag->html_content_expression) ?
                    ', ["default" => ob_get_clean()]+array_shift($__s)' : '',
            );
        }
        return tag::tag_close($tag->tagname); // sprintf('</%s>', $tag->tagname);
    }

    function php_bindings($ep, $for_component = false) {
        $php = [];
        foreach ($this->tag->bindings as $name => $expression) {
            if ($for_component) $name = str_replace("-", "_", $name);
            $php[] = sprintf('"%s"=> %s', $name, $this->compile_expression($expression, $ep));
            // $this->ep->evaluate("%s", $__blockdata + $__data)
        }
        return '[' . join(", ", $php) . ']';
    }

    function php_foreach($ep): string {
        // dbg("++foreach++", $this);
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
