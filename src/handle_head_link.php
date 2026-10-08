<?php

namespace phuety;

use Dom\HTMLElement;

// handles css links in html head 
// everything can be made at compile time
// not like asset collect at runtime
class handle_head_link {

    public function __construct(public asset_compiler $asset_compiler, public bool $remove_node = false) {
    }

    public function handle(HtmlElement $node, parts $parts): bool {
        if ($node->tagName != "LINK") return false;
        $attrs = dom::attributes($node);
        if ($attrs["rel"] != "stylesheet") return false;
        if (isset($attrs["nobuild"])) {
            $node->removeAttribute("nobuild");
            return true;
        }
        // if (!str_starts_with($attrs["href"], "@assets")) return true;
        $new_name = $this->asset_compiler->compile_head_css($attrs["href"]);
        $node->setAttribute("href", $new_name);
        return true;
    }
}
