<?php

namespace phuety;

use Closure;

class render_component {

    public string $name;
    public string $uid;

    public array $assets = [];
    public ?array $components = null;

    public function xxx__construct() {
        //   $this->uid = uniqid();
        $this->name = str_replace('_component', '', static::class);
        /*if ($this->has_template) {
            if (!$tpl) $tpl = file_get_contents($cbase . '/' . $this->name . '.html');
            $this->load_dom($tpl);
        }
        $this->renderer = new dom_render('', ['strrev' => 'strrev']);
        */
    }

    public function collect_assets(asset $assetholder) {
        foreach ($this->assets as $asset) {
            $assetholder->push($this->uid, $asset);
        }
    }
    public function run($runner, phuety $engine, phuety_context $context, data_container $props_container, array $slots = [], ?asset $assetholder = null): void {
        // dbg("++ all helper", $engine->helper);
        // $props_container = new data_container($props, $engine->helper);
        print $this->render($props_container, $slots, $props_container, $context, $assetholder, $runner);
    }


    public function render(data_container $props, array $slots, data_container $helper, phuety_context $phuety, asset $assetholder, $runner): string {
        return "";
    }
}
