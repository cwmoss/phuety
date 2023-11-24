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
    public bool $is_layout = false;
    public bool $is_start = false;
    public ?DOMDocument $pagedom = null;
    public dom_render $renderer;
    public phuety $engine;
    public $dom = null;
    public ?props $propholder = null;
    public ?asset $assetholder = null;

    public function __construct(public string $cbase, $tpl = null) {
        //   $this->uid = uniqid();
        $this->name = str_replace('_component', '', static::class);
        if ($this->has_template) {
            if (!$tpl) $tpl = file_get_contents($cbase . '/' . $this->name . '.html');
            $this->load_dom($tpl);
        }
        $this->renderer = new dom_render('', ['strrev' => 'strrev']);
    }

    static function new_from_string(string $tpl, string $cbase): component {
        return new self($cbase, $tpl);
    }

    static function load_class($name, $dir) {

        $cname = $name . '_component';
        require_once($dir . '/' . $cname . '.php');
        $comp = new $cname($dir);
        // $comp->load_dom();
        return $comp;
    }

    public function load_dom($html) {
        if ($this->is_layout) {
            $dom = dom::get_document($html);
        } else {
            $dom = dom::get_fragment($html);
        }
        $this->dom = $dom;
    }

    public function start_running(array $props = []) {
        // dom::d("start run dom", $this->dom);
        #var_dump($props);
        $this->is_start = true;
        if (!$this->propholder) {
            $this->propholder = new props;
        }
        $dom = $this->run($props);
        if ($this->pagedom) {
            $this->travel_phuety($this->pagedom, $this->propholder);
            return $this->pagedom->saveHTML();
        }
        if ($this->is_layout) {
            $this->travel_phuety($dom, $this->propholder);
            return $dom->saveHTML();
        }
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
        return ['props' => $props] + $props;
    }

    public function run(array $props = [], DOMNodeList $children = null) {
        // push assets
        foreach ($this->assets as $asset) {
            $this->assetholder->push($this->uid, $asset);
        }
        //print_r($this->assetholder);
        // renderless?
        if (!$this->has_template) {
            ob_start();
            $this->run_code($props);
            $html = ob_get_clean();
            $dom = dom::get_fragment($html);
        } else {

            $dom = $this->dom->cloneNode(true);
            dom::register_class($dom);
            $result = $this->run_code($props);
            #var_dump($result);
            [$data, $methods] = $this->separate_functions($result);
            #var_dump($data);

            if ($this->is_layout) {
                # $html = $this->renderer->render_page($data, $methods);
                $this->renderer->render_page_dom($dom, $this->propholder, $data, $methods);
            } else {
                $this->renderer->render_dom($dom, $this->propholder, $data, $methods);
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
        }
        $this->travel_nodes($dom->documentElement, $dom, $this->propholder);
        $this->replace_slot($dom, $children, $this->propholder);
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
            $dom = dom::get_document($html);
        } else {
            $dom = dom::get_fragment($html);
        }

        $this->travel_nodes($dom->documentElement, $dom);
        $this->replace_slot($dom, $children);
        # compiler::d("after replace " . static::class, $dom);
        return $dom;
    }

    public function replace_slot($dom, $children, props $props) {
        if (!$children) return;
        $ndom = new DOMDocument();
        $ndom->registerNodeClass("DOMElement", custom_domelement::class);
        foreach ($children as $ch) {
            # print "+++ children import " . ($ch->tagName ?? null) . " \n";
            $nch = $ndom->importNode($ch, true);
            $ndom->appendChild($nch);
        }

        # compiler::d('-new dom before travel-', $ndom);
        // print_r($ndom->documentElement);
        $this->travel_nodes($ndom, $ndom, $props, true);
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

    public function travel_phuety(DOMNode $node, props $props) {
        if ($node instanceof DOMNodeList) {
            # print "travel list\n";
            foreach (iterator_to_array($node) as $childNode) {
                $this->travel_phuety($childNode, $props);
            }
        }
        if ($node->nodeType == \XML_DOCUMENT_NODE) {
            # print "travel doc\n";
            foreach (iterator_to_array($node->childNodes) as $childNode) {
                $this->travel_phuety($childNode, $props);
            }
            return;
        }
        if (!($node->nodeType == \XML_ELEMENT_NODE || $node->nodeType == \XML_TEXT_NODE)) {
            # print "travel break\n";
            return;
        }

        if (($node->tagName ?? null) && $this->engine->is_component($node->tagName)) {
            # print "+++ handle component {$node->tagName}\n";
            #if (str_starts_with($node->tagName, 'phuety-')) return;
            $this->handle_component($node->tagName, $node, $node->ownerDocument, $props, false);
            return;
        };
        foreach (iterator_to_array($node->childNodes) as $childNode) {
            # print "travel child len {$node->childNodes->length}\n";
            $this->travel_phuety($childNode, $props);
        }
    }

    public function travel_nodes(DOMNode $node, $dom, props $props, $slotmode = false) {
        # print("travel $node->nodeType \n");
        if ($node instanceof DOMNodeList) {
            # print "travel list\n";
            foreach (iterator_to_array($node) as $childNode) {
                $this->travel_nodes($childNode, $dom, $props, $slotmode);
            }
        }
        if ($node->nodeType == \XML_DOCUMENT_NODE) {
            # print "travel doc\n";
            foreach (iterator_to_array($node->childNodes) as $childNode) {
                $this->travel_nodes($childNode, $dom, $props, $slotmode);
            }
            return;
        }
        if (!($node->nodeType == \XML_ELEMENT_NODE || $node->nodeType == \XML_TEXT_NODE)) {
            # print "travel break\n";
            return;
        }

        if (($node->tagName ?? null) && $this->engine->is_component($node->tagName)) {
            # print "+++ handle component {$node->tagName}\n";
            if (str_starts_with($node->tagName, 'phuety-')) return;
            $this->handle_component($node->tagName, $node, $dom, $props, $slotmode);
            return;
        };
        foreach (iterator_to_array($node->childNodes) as $childNode) {
            # print "travel child len {$node->childNodes->length}\n";
            $this->travel_nodes($childNode, $dom, $props, $slotmode);
        }
    }

    public function handle_component($tagname, DOMNode $node, $dom, props $props, $slotmode = false) {
        // var_dump($this->engine);
        $component = $this->engine->get_component($tagname);
        $component->propholder = $props;
        # print "\n=== +handle this {$this->name} compname {$component->name} start? -{$this->is_start}- layout? -{$this->is_layout}- slotmode? -{$slotmode}-\n";
        $attrs = dom::attributes($node);
        $attrs += $props->get($attrs['props'] ?? null);
        // $node->hey();

        #print_r($attrs);
        #print_r($props);
        // $props = $attrs + $node->data;


        /*foreach ($props as $k => $v) {
            if ($k[0] == ':') {
                $props[ltrim($k, ':')] = $v;
            }
        }*/
        $newdom = $component->run($attrs, $node->childNodes);
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
}
