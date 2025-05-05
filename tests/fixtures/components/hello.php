<?php

namespace bary\components;

use phuety\asset;
use phuety\component;
use phuety\data_container;
use phuety\phuety_context;

use function phuety\dbg;

class hello extends component {

    public bool $has_code = true;

    public function run_code(data_container $props, array $slots, data_container $helper, phuety_context $phuety, asset $assetholder): array {
        print "<div>hi world</div>";
        return [];
    }
}
