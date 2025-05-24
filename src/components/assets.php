<?php

namespace phuety\components;

use phuety\asset;
use phuety\component;
use phuety\data_container;
use phuety\phuety_context;

use function phuety\dbg;

class assets extends component {

    public bool $has_code = true;

    public function run_code(data_container $props, array $slots, data_container $helper, phuety_context $phuety, asset $assetholder): array {
        $position = isset($props->body) ? 'body' : 'head';
        // print $this->assets->get($position);
        // print $props['$asset']->get($position);
        dbg("++ run assets", $position, $assetholder, " --- ", $assetholder->get($position));
        print $assetholder->get($position);
        return [];
    }
}
