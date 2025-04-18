<?php

namespace phuety;

use Dom\HTMLElement;

class handle_css {

    public function __construct(public bool $simple_scope = false, public bool $remove_node = true) {
    }

    public function handle(HtmlElement $node, parts $parts): bool {
        if ($node->tagName != "STYLE") return false;
        $attrs = dom::attributes($node);
        if (!isset($attrs['global'])) {
            $css = str_replace('root', '&.root', (string) $node->textContent);
            $css = sprintf(".%s{\n%s\n}", $parts->uid, $css);
            $parts->css = $css;
        } else {
            $node->removeAttribute('global');
            $parts->assets[] = ['style', 'head', dom::attributes($node), $node->ownerDocument->saveHtml($node)];
        }
        return true;
    }
}
