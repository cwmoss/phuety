<?php

namespace slow;

use DOMNode;
use DOMNodeList;
use DOMDocument;
use WMDE\VueJsTemplating\Component as vcomponent;


class component {

    public string $name;
    public string $uid;
    public vcomponent $renderer;
    public compiler $compiler;

    public function __construct(public string $cbase) {
        //   $this->uid = uniqid();
        $this->name = str_replace('_component', '', static::class);
        $tpl = file_get_contents($cbase . '/' . $this->name . '.html');
        $this->renderer = new vcomponent($tpl, ['strrev' => 'strrev']);
    }

    public function run(array $props = [], DOMNodeList $children = null) {
        $file = $this->cbase . '/' . $this->name . '.run.php';
        include($file);
        $result = get_defined_vars();
        $html = $this->renderer->render($result);
        print "slot?";
        //print_r($children);
        $dom = compiler::get_fragment($html);

        $this->travel_nodes($dom->documentElement, $dom);
        $this->replace_slot($dom, $children);
        return $dom;
    }

    public function replace_slot($dom, $children) {
        if (!$children) return;
        $ndom = new DOMDocument();
        foreach ($children as $ch) {
            print "+++ children import $ch->tagName \n";
            $nch = $ndom->importNode($ch, true);
            $ndom->appendChild($nch);
        }
        print "+++ ndom" . $ndom->saveHTML();
        print_r($ndom->documentElement);
        $this->travel_nodes($ndom, $ndom);
        $slottags = $dom->getElementsByTagName('slot');
        var_dump($slottags->length);

        if ($slottags->length == 1) {
            $slot = $slottags->item(0);
            $slotdom = $dom->importNode($ndom->documentElement, true);
            $slot->parentNode->replaceChild($slotdom, $slot);
        }
    }

    public function travel_nodes(DOMNode $node, $dom) {
        print_r($node);
        if ($node instanceof DOMNodeList) {
            foreach (iterator_to_array($node) as $childNode) {
                $this->travel_nodes($childNode, $dom);
            }
        }
        if ($node->nodeType != \XML_ELEMENT_NODE) return;

        if (str_starts_with($node->tagName, 'p-')) {
            print "+++handle comp {$node->tagName}\n";
            $this->handle_component($node->tagName, $node, $dom);
            return;
        };
        foreach (iterator_to_array($node->childNodes) as $childNode) {
            $this->travel_nodes($childNode, $dom);
        }
    }

    public function handle_component($coname, DOMNode $node, $dom) {
        $name = str_replace('p-', '', $coname);
        $component = $this->compiler->get_component($name);
        $newdom = $component->run(compiler::attributes($node), $node->childNodes);
        print_r($newdom);
        $newnode = $dom->importNode($newdom->documentElement, true);

        $node->parentNode->replaceChild($newnode, $node);
    }
    static function load($name, $dir) {
        $cname = $name . '_component';
        require_once($dir . '/' . $cname . '.php');
        $comp = new $cname($dir);
        return $comp;
    }

    static public function create($name, $dir, $parts, $uid) {
        $tpl = file_get_contents(__DIR__ . '/_component.php');
        $repl = ['NAME' => $name, 'UID' => $uid];
        $tpl = str_replace(array_keys($repl), array_values($repl), $tpl);
        file_put_contents($dir . '/' . $name . '_component.php', $tpl);
        $css = sprintf(".%s{\n%s\n}", $uid, $parts['css']);
        file_put_contents($dir . '/' . $name . '.css', $css);
        $php = '<?php ' . $parts['php'];
        file_put_contents($dir . '/' . $name . '.run.php', $php);
        $vue = sprintf('<div>%s</div>', $parts['vue']);
        file_put_contents($dir . '/' . $name . '.html', $vue);
        return $repl['UID'];
    }
}
