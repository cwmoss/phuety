<?php
namespace compiled;

use phuety\component;
use phuety\data_container;
use phuety\phuety;
use phuety\tag;
use phuety\asset;
use phuety\phuety_context;

use function phuety\dbg;



/**
 * /Users/rw/dev/playground/phuety/docs/src/components/top_nav.phue.php ~ 10
 */

class top_nav_component extends component {
    public string $uid = "top_nav---2mVFmV";
    public bool $is_layout = false;
    public string $name = "top_nav";
    public string $tagname = "top.nav";
    public bool $has_template = true;
    public bool $has_code = true;
    public bool $has_style = false;
    public array $assets = array (
);
    public array $custom_tags = array (
);
    public int $total_rootelements = 1;
    public ?array $components = NULL;

    public function run_code(data_container $props, array $slots, data_container $helper, phuety_context $phuety, asset $assetholder): array{
        // dbg("++ props for component", $this->name, $props);

$chapters = $helper->get('chapter_index');
// var_dump($chapters);die();
$current_section = $props->current->dir ? basename($props->current->dir) : basename($chapters->index[0]->_file->dir);

        return get_defined_vars();
    }

    public function render($__runner, data_container $__d, array $slots=[]):void {
        // ob_start();
        // if($this->is_layout) print '<!DOCTYPE html>';
        $__s = [];
        ?><?= tag::tag_open_merged_attrs("nav", [], array (
) , $__d->_get("props")) ?>
    <?php foreach($__d->_get("chapters")->index as  $section){$__d->_add_block(["section"=>$section ]); ?><?= tag::tag_open_merged_attrs("details", ["open"=> ((($__d->_get("section")->sid == $__d->_get("current_section"))) ? ("open") : ("")), "class"=> ["active" => ($__d->_get("sid") == $__d->_get("current_section"))]], array (
) ) ?>
        <summary><?= tag::h($__d->_get("section")->title) ?></summary>
        <?php foreach($__d->_get("section")->c as  $chapter){$__d->_add_block(["chapter"=>$chapter ]); ?><?= tag::tag_open_merged_attrs("a", ["href"=> $__d->_get("helper")->path($__d->_get("chapter")), "class"=> ["active" => ($__d->_get("chapter")->_file->path == $__d->_get("current")->path)]], array (
) ) ?><?= tag::h($__d->_get("chapter")->title) ?></a><?php $__d->_remove_block();} ?>
    </details><?php $__d->_remove_block();} ?>
</nav><?php // return ob_get_clean();
        // dbg("+++ assetsholder ", $this->is_start, $this->assetholder);
    }

    // public function debug_info(){
    //    return /Users/rw/dev/playground/phuety/docs/src/components/top_nav.phue.php ~ 10;
    // }
}
