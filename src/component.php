<?php

namespace phuety;

use Closure;
use DOMNode;
use DOMNodeList;
use DOMDocument;
use WMDE\VueJsTemplating\Component as vcomponent;


class component {

    public string $name;
    public string $uid;
    public bool $is_layout;
    public bool $is_start = false;
    public ?DOMDocument $pagedom = null;
    public vcomponent $renderer;
    public compiler $compiler;
    public $dom = null;

    public function __construct(public string $cbase) {
        //   $this->uid = uniqid();
        $this->name = str_replace('_component', '', static::class);
        $tpl = file_get_contents($cbase . '/' . $this->name . '.html');
        $this->load_dom($tpl);
        $this->renderer = new vcomponent('', ['strrev' => 'strrev']);
    }

    public function load_dom($html) {
        if ($this->is_layout) {
            $dom = compiler::get_document($html);
        } else {
            $dom = compiler::get_fragment($html);
        }
        $this->dom = $dom;
    }

    public function start_running(array $props = []) {
        $this->is_start = true;
        $dom = $this->run($props);
        if ($this->pagedom) return $this->pagedom->saveHTML();

        // fragment with "ok" root 
        return substr(trim($dom->saveHtml()), 4, -5);
    }

    public function separate_functions($input) {
        $data = $fun = [];
        foreach ($input as $key => $value) {
            if ($value instanceof Closure) {
                $fun[$key] = $value;
            } else {
                $data[$key] = $value;
            }
        }
        return [$data, $fun];
    }

    public function run_code(array $props) {
    }

    public function run(array $props = [], DOMNodeList $children = null) {
        $dom = $this->dom->cloneNode(true);
        $result = $this->run_code($props);
        [$data, $methods] = $this->separate_functions($result);

        if ($this->is_layout) {
            # $html = $this->renderer->render_page($data, $methods);
            $this->renderer->render_page_dom($dom, $data, $methods);
        } else {
            $this->renderer->render_dom($dom, $data, $methods);
        }

        // var_dump($this->is_layout);
        //print "html result: $html\n";
        #print "slot?\n";
        //print_r($children);
        // layouts are different 
        if ($this->is_layout) {
            #    $dom = compiler::get_document($html);
        } else {
            #    $dom = compiler::get_fragment($html);
        }

        $this->travel_nodes($dom->documentElement, $dom);
        $this->replace_slot($dom, $children);
        # compiler::d("after replace " . static::class, $dom);
        return $dom;
    }


    public function run0(array $props = [], DOMNodeList $children = null) {
        // print "\nrunning... " . static::class . "\n";
        $file = $this->cbase . '/' . $this->name . '.run.php';
        include($file);
        $result = get_defined_vars();
        if ($this->is_layout) {
            $html = $this->renderer->render_page($result);
        } else {
            $html = $this->renderer->render($result);
        }


        #print "html result: $html\n";
        #print "slot?\n";
        //print_r($children);
        // layouts are different 
        if ($this->is_layout) {
            $dom = compiler::get_document($html);
        } else {
            $dom = compiler::get_fragment($html);
        }

        $this->travel_nodes($dom->documentElement, $dom);
        $this->replace_slot($dom, $children);
        # compiler::d("after replace " . static::class, $dom);
        return $dom;
    }

    public function replace_slot($dom, $children) {
        if (!$children) return;
        $ndom = new DOMDocument();
        foreach ($children as $ch) {
            # print "+++ children import " . ($ch->tagName ?? null) . " \n";
            $nch = $ndom->importNode($ch, true);
            $ndom->appendChild($nch);
        }

        # compiler::d('-new dom before travel-', $ndom);
        // print_r($ndom->documentElement);
        $this->travel_nodes($ndom, $ndom, true);
        # compiler::d('-new dom after travel-', $ndom);
        // print $ndom->saveHTML();
        $slottags = $dom->getElementsByTagName('slot');
        # var_dump($slottags->length);

        if ($slottags->length == 1) {
            #    print "+++ slot REPLACE\n";
            $slot = $slottags->item(0);
            foreach ($ndom->childNodes as $c) {
                $slotdom = $dom->importNode($c, true);
                $slot->parentNode->insertBefore($slotdom, $slot);
            }
            $slot->parentNode->removeChild($slot);
            // compiler::d("-new nodes for slot-", $slotdom);
        }
    }

    public function travel_nodes(DOMNode $node, $dom, $slotmode = false) {
        # print("travel $node->nodeType \n");
        if ($node instanceof DOMNodeList) {
            # print "travel list\n";
            foreach (iterator_to_array($node) as $childNode) {
                $this->travel_nodes($childNode, $dom, $slotmode);
            }
        }
        if ($node->nodeType == \XML_DOCUMENT_NODE) {
            # print "travel doc\n";
            foreach (iterator_to_array($node->childNodes) as $childNode) {
                $this->travel_nodes($childNode, $dom, $slotmode);
            }
            return;
        }
        if (!($node->nodeType == \XML_ELEMENT_NODE || $node->nodeType == \XML_TEXT_NODE)) {
            # print "travel break\n";
            return;
        }

        if (($node->tagName ?? null) && str_starts_with($node->tagName, 'p-')) {
            # print "+++ handle component {$node->tagName}\n";
            $this->handle_component($node->tagName, $node, $dom, $slotmode);
            return;
        };
        foreach (iterator_to_array($node->childNodes) as $childNode) {
            # print "travel child len {$node->childNodes->length}\n";
            $this->travel_nodes($childNode, $dom, $slotmode);
        }
    }

    public function handle_component($coname, DOMNode $node, $dom, $slotmode = false) {
        $name = str_replace('p-', '', $coname);
        $component = $this->compiler->get_component($name);
        # print "\n=== +handle this {$this->name} compname {$component->name} start? -{$this->is_start}- layout? -{$this->is_layout}- slotmode? -{$slotmode}-\n";

        $newdom = $component->run(compiler::attributes($node), $node->childNodes);
        // print_r($newdom);
        # print "\n=== -handle this {$this->name} compname {$component->name} start? -{$this->is_start}- layout? -{$this->is_layout}- slotmode? -{$slotmode}-\n";

        # compiler::d("newdom handle_component", $newdom);

        //var_dump($component->is_layout);
        // if we render a html page, we save the dom and have finished
        if ($this->is_start && $component->is_layout && !$slotmode) {
            // print $newdom->saveHTML();
            $this->pagedom = $newdom;
            return;
        }
        // all children of <ok>
        foreach ($newdom->documentElement->childNodes as $c) {
            $newnode = $dom->importNode($c, true);
            $node->parentNode->insertBefore($newnode, $node);
        }
        $node->parentNode->removeChild($node);
    }
    static function load($name, $dir) {
        $cname = $name . '_component';
        require_once($dir . '/' . $cname . '.php');
        $comp = new $cname($dir);
        // $comp->load_dom();
        return $comp;
    }

    static public function create($name, $dir, $parts) {
        # print "create component $name";
        // print_r($parts);
        $tpl = file_get_contents(__DIR__ . '/_component.php');

        [$php, $use] = self::get_use_statements($parts['php']);
        $repl = [
            'NAME' => $name, 'UID' => $parts['uid'],
            'ISLAYOUT' => $parts['is_layout'] ? 'true' : 'false',
            'PHPCODE' => $php,
            'USESTATEMENTS' => $use
        ];


        $tpl = str_replace(array_keys($repl), array_values($repl), $tpl);
        file_put_contents($dir . '/' . $name . '_component.php', $tpl);
        $css = sprintf(".%s{\n%s\n}", $parts['uid'], $parts['css']);
        file_put_contents($dir . '/' . $name . '.css', $css);
        // $php = '<?php ' . $parts['php'];
        // file_put_contents($dir . '/' . $name . '.run.php', $php);
        $vue = sprintf('%s', $parts['vue']);
        file_put_contents($dir . '/' . $name . '.html', $vue);
        return $repl['UID'];
    }

    static public function get_use_statements($code) {
        $use = preg_match_all("/^\s*use\s+[^;]+;\s*$/ms", $code, $mat, \PREG_SET_ORDER);
        if (!$mat) return [$code, ""];

        $use = join("\n", array_map(fn ($el) => $el[0], $mat));
        $code = preg_replace("/^\s*use\s+[^;]+;\s*$/ms", "", $code);
        return [$code, $use];
    }
}
