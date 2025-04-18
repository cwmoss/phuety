<?php

namespace phuety;

use Dom\HTMLElement;

class handle_script {

    public function __construct(public string $baseurl = '/assets/generated/', public bool $remove_node = true) {
    }

    public function handle(HtmlElement $node, parts $parts): bool {
        if ($node->tagName != "SCRIPT") return false;
        $attrs = dom::attributes($node);
        $position = (isset($attrs['head']) ? 'head' : null);
        if (is_null($position)) $position = 'body';
        $node->removeAttribute('head');
        // convert embeded to external?
        if (!isset($attrs['src'])) {
            dbg("++ js embed => external");
            $name = $parts->uid . '-' . count($parts->js) . '.js';
            $parts->js[$name] = (string) $node->textContent;
            dbg("+++ embed js $name", $parts->js[$name]);
            $node->textContent = null;
            $node->setAttribute('src', $this->baseurl . $name);
        } else {
            // todo: cache buster
            if ($attrs['src'] ?? null && $attrs['src'][0] == '/') {
                $node->setAttribute('src', $attrs['src'] . '?' . time());
            }
        }
        $parts->assets[] = ['script', $position, dom::attributes($node), $node->ownerDocument->saveHTML($node)];
        return true;
    }
}
