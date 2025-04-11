<?php
namespace compiled;

use phuety\component;
use phuety\data_container;
use phuety\tag;

USESTATEMENTS

class NAME_component extends component {
    public string $uid = "UID";
    public bool $is_layout = ISLAYOUT;
    public string $name = "NAME";
    public bool $has_template = HAS_TEMPLATE;
    public bool $has_code = HAS_CODE;
    public bool $has_style = HAS_STYLE;
    public array $assets = ASSETS;

    function run_code(array $props, array $slots = [], array $helper = []){
        dbg("++ props for component", $this->name, $props);
        PHPCODE
        return get_defined_vars() + $props;
    }

    function render(array $__data=[], array $slots=[], array $helper = []):void {
        $__d = new data_container($__data, $helper);
        // ob_start();
        // if($this->is_layout) print '<!DOCTYPE html>';
        ?>
        RENDER
        <?php // return ob_get_clean();
        // dbg("+++ assetsholder ", $this->is_start, $this->assetholder);
    }
}
