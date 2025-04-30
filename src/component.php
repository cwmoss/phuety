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
    public ?array $components = null;

    // public $slot;

    // public bool $is_start = false;

    // expression parser
    protected $ep;
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

    public function set_engine($e) {
        # $this->engine = $e;
    }

    public function set_ep($ep) {
        # $this->ep = $ep;
    }
    static function new_from_string(string $tpl): component {
        return new self($tpl);
    }
    public function collect_assets(asset $assetholder) {
        foreach ($this->assets as $asset) {
            $assetholder->push($this->uid, $asset);
        }
    }
    public function run($runner, phuety $engine, phuety_context $context, array $props = [], array $slots = [], ?asset $assetholder = null): void {
        // dbg("++ all helper", $engine->helper);
        $props_container = new data_container($props, $engine->helper);
        $local = $this->run_code($props_container, $slots, $props_container, $context, $assetholder);
        // $props_container->_add_phuety_context($context);
        if ($local) $props_container->_add_local($local);
        $this->render($runner, $props_container, $slots);
    }

    static public function xxxget_runner(phuety $engine, self $component) {
        $assets = $component->assets;

        return function ($runner, array $props = [], array $slots = [], ?asset $assetholder = null) use ($component, $engine, $assets) {
            if (false && $assetholder) foreach ($assets as $asset) {
                $assetholder->push($component->uid, $asset);
            }

            $props_container = new data_container($props, $engine->helper);
            $local = $component->run_code($props_container, $slots, $props_container, $context, $assetholder);
            if ($local) $props_container->_add_local($local);
            $component->render($runner, $props_container, $slots);
        };
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

    public function run_code(data_container $props, array $slots, data_container $helper, phuety_context $phuety, asset $assetholder): array {
        return [];
    }

    public function render($__runner, data_container $__d, array $slots = []): void {
        // return "";
    }

    // public function post_components(HTMLDocument $dom, $props) {
    //     foreach ($dom->querySelectorAll("link[rel=assets]") as $anode) {
    //         $this->handle_component("phuety-assets", $anode, $anode->ownerDocument, $props, false);
    //     }
    // }

}
