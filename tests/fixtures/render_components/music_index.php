<?php

namespace my\music\app;

use phuety\data_container;
use phuety\phuety_context;
use phuety\asset;
use phuety\render_component;

class music_index extends render_component {

    public function render(data_container $props, array $slots, data_container $helper, phuety_context $phuety, asset $assetholder, $runner): string {
        $user = $props->globals->user ?? "";
        return "<div>index of {$props->category}$user</div>";
    }
}
