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
    public array $custom_tags = CUSTOM_TAGS;

    function run_code(data_container $props, array $slots = [], data_container $helper){
        dbg("++ props for component", $this->name, $props);
        PHPCODE
        return get_defined_vars();
    }

    function render(data_container $__d, array $slots=[]):void {
        // ob_start();
        // if($this->is_layout) print '<!DOCTYPE html>';
        ?>
        RENDER
        <?php // return ob_get_clean();
        // dbg("+++ assetsholder ", $this->is_start, $this->assetholder);
    }
}
