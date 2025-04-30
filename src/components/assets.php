<?php

namespace phuety\components;

use phuety\asset;
use phuety\component;
use phuety\data_container;
use function phuety\dbg;

class assets extends component {

    public bool $has_code = true;

    public function run_code(data_container $props, array $slots, data_container $helper, asset $assetholder): array {
        $position = isset($props->head) ? 'head' : 'body';
        // print $this->assets->get($position);
        // print $props['$asset']->get($position);
        dbg("++ run assets", $position, $assetholder, " --- ", $assetholder->get($position));
        print $assetholder->get($position);
        return [];
    }
}
