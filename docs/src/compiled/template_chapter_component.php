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
 * /Users/rw/dev/playground/phuety/docs/src/templates/chapter.phue.php ~ 18
 */

class template_chapter_component extends component {
    public string $uid = "template_chapter---TsmQtz";
    public bool $is_layout = false;
    public string $name = "template_chapter";
    public string $tagname = "template.chapter";
    public bool $has_template = true;
    public bool $has_code = true;
    public bool $has_style = true;
    public array $assets = array (
);
    public array $custom_tags = array (
);
    public int $total_rootelements = 1;
    public ?array $components = array (
  0 => 'layout.default',
  1 => 'sft.markdown',
);

    public function run_code(data_container $props, array $slots, data_container $helper, phuety_context $phuety, asset $assetholder): array{
        // dbg("++ props for component", $this->name, $props);

dbg("... template all props", $props->page);
$html = "<em>hi</em>";
// $html = $markdown("**hello**");

        return get_defined_vars();
    }

    public function render($__runner, data_container $__d, array $slots=[]):void {
        // ob_start();
        // if($this->is_layout) print '<!DOCTYPE html>';
        $__s = [];
        ?><?php array_unshift($__s, []); ob_start(); ?>
    <article>
        <h1><?= tag::h($__d->_get("page")->title) ?></h1>
        <?php $__runner($__runner, "sft.markdown", $__d->_get("phuety")->with($this->tagname, "sft.markdown"), ["body"=> $__d->_get("page")->mdbody] + array (
) ); ?>
    </article>

    <aside>
        <h4>ON THIS PAGE</h4>
        <?php $__runner($__runner, "sft.markdown", $__d->_get("phuety")->with($this->tagname, "sft.markdown"), ["body"=> $__d->_get("page")->mdbody] + array (
  'toc' => '',
) ); ?>
    </aside>
<?php $__runner($__runner, "layout.default", $__d->_get("phuety")->with($this->tagname, "layout.default"), ["title"=> $__d->_get("page")->title, "page"=> $__d->_get("page")] + array (
  'class' => 'template_chapter---TsmQtz root',
) , ["default" => ob_get_clean()]+array_shift($__s)); ?>

<?php // return ob_get_clean();
        // dbg("+++ assetsholder ", $this->is_start, $this->assetholder);
    }

    // public function debug_info(){
    //    return /Users/rw/dev/playground/phuety/docs/src/templates/chapter.phue.php ~ 18;
    // }
}
