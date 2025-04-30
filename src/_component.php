<?php
namespace compiled;

use phuety\component;
use phuety\data_container;
use phuety\phuety;
use phuety\tag;
use phuety\asset;
use function phuety\dbg;

USESTATEMENTS

class NAME_component extends component {
    public string $uid = "UID";
    public bool $is_layout = ISLAYOUT;
    public string $name = "NAME";
    public bool $has_template = HAS_TEMPLATE;
    public bool $has_code = HAS_CODE;
    public bool $has_style = HAS_STYLE;
    public array $assets = ASSETS;
    public array $custom_tags = CUSTOM_TAGS;
    public int $total_rootelements = TOTAL_ROOTELEMENTS;

    public function run_code(data_container $props, array $slots, data_container $helper, asset $assetholder): array{
        // dbg("++ props for component", $this->name, $props);PHPCODE
        return get_defined_vars();
    }

    public function render($__runner, data_container $__d, array $slots=[]):void {
        // ob_start();
        // if($this->is_layout) print '<!DOCTYPE html>';
        $__s = [];
        ?>RENDER<?php // return ob_get_clean();
        // dbg("+++ assetsholder ", $this->is_start, $this->assetholder);
    }

    public function debug_info(){
        return DEBUG_INFO;
    }
}
