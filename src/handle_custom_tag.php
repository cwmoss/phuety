<?php

namespace phuety;

use Dom\HTMLElement;

class handle_custom_tag {

    public function __construct(public string $name, public string $baseurl = '/assets/generated/', public bool $remove_node = true) {
    }

    public function handle(HtmlElement $node, parts $parts): bool {
        $name = strtolower($node->tagName);
        if ($name != $this->name) return false;
        $attrs = dom::attributes($node);
        // if($attrs['href'])
        $parts->custom[$name] = [
            "name" => $name,
            "attrs" => $attrs,
            "content" => $node->innerHTML
        ];
        return true;
    }
}
