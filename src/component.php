<?php

namespace phuety;

use Closure;
use Dom\Document;
use Dom\Element;
use Dom\HTMLDocument;
use Dom\NodeList;
use Dom\Node;

use WMDE\VueJsTemplating\Component as vcomponent;

class component {

    public string $name;
    public string $uid;

    public bool $is_layout = false;

    public bool $has_template = false;
    public bool $has_code = false;
    public bool $has_style = false;
    public array $assets = [];

    // public $slot;

    public bool $is_start = false;
    public phuety $engine;
    // expression parser
    public $ep;
    // public $dom = null;
    // public ?props $propholder = null;
    public ?asset $assetholder = null;

    public function __construct() {
        //   $this->uid = uniqid();
        $this->name = str_replace('_component', '', static::class);
        /*if ($this->has_template) {
            if (!$tpl) $tpl = file_get_contents($cbase . '/' . $this->name . '.html');
            $this->load_dom($tpl);
        }
        $this->renderer = new dom_render('', ['strrev' => 'strrev']);
        */
    }

    static function new_from_string(string $tpl): component {
        return new self($tpl);
    }

    public function run(array $props = [], array $slots = []) {
        // TODO: optimize
        foreach ($this->assets as $asset) {
            $this->assetholder->push($this->uid, $asset);
        }
        $props = $this->run_code($props, $slots, $this->engine->helper);
        $res = $this->render($props, $slots, $this->engine->helper);
        return $res;
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

    public function run_code(array $props, array $slots = [], array $helper = []) {
        return ['props' => $props] + $props;
    }

    public function render(array $__data = [], array $slots = [], array $helper = []): void {
        // return "";
    }

    // public function post_components(HTMLDocument $dom, $props) {
    //     foreach ($dom->querySelectorAll("link[rel=assets]") as $anode) {
    //         $this->handle_component("phuety-assets", $anode, $anode->ownerDocument, $props, false);
    //     }
    // }

}
