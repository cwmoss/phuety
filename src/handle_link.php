<?php

namespace phuety;

use Dom\HTMLElement;

class handle_link {

    public function __construct(public string $baseurl = '/assets/generated/', public bool $remove_node = true) {
    }

    public function handle(HtmlElement $node, parts $parts): bool {
        if ($node->tagName != "LINK") return false;
        $attrs = dom::attributes($node);
        $parts->assets[] = ['link', 'head', $attrs, $node->ownerDocument->saveHTML($node)];
        return true;
    }
}
