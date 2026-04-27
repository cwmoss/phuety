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
 * /Users/rw/dev/playground/phuety/docs/src/pages/hello.phue.php ~ 
 */

class page_hello_component extends component {
    public string $uid = "page_hello---ABZ3GE";
    public bool $is_layout = false;
    public string $name = "page_hello";
    public string $tagname = "page.hello";
    public bool $has_template = true;
    public bool $has_code = false;
    public bool $has_style = true;
    public array $assets = array (
);
    public array $custom_tags = array (
);
    public int $total_rootelements = 1;
    public ?array $components = array (
  0 => 'layout.default',
  1 => 'sft.image',
);

    public function run_code(data_container $props, array $slots, data_container $helper, phuety_context $phuety, asset $assetholder): array{
        // dbg("++ props for component", $this->name, $props);
        return get_defined_vars();
    }

    public function render($__runner, data_container $__d, array $slots=[]):void {
        // ob_start();
        // if($this->is_layout) print '<!DOCTYPE html>';
        $__s = [];
        ?><?php array_unshift($__s, []); ob_start(); ?>
  <article>
    <h1>hello</h1>

    <p>to the world</p>

    <figure>
      <?php $__runner($__runner, "sft.image", $__d->_get("phuety")->with($this->tagname, "sft.image"), [] + array (
  'src' => 'src/pages/kitty.jpg',
  'size' => '600x',
  'alt' => 'this is the cat',
) ); ?>

      <figcaption>Foto von <a href="https://unsplash.com/de/@yerlinmatu?utm_content=creditCopyText&amp;utm_medium=referral&amp;utm_source=unsplash">Yerlin Matu</a>
        auf <a href="https://unsplash.com/de/fotos/flachfokusfotografie-von-weissen-und-braunen-katzen-GtwiBmtJvaU?utm_content=creditCopyText&amp;utm_medium=referral&amp;utm_source=unsplash">Unsplash</a>
      </figcaption>
    </figure>

  </article>
<?php $__runner($__runner, "layout.default", $__d->_get("phuety")->with($this->tagname, "layout.default"), ["title"=> $__d->_get("page")->title, "page"=> $__d->_get("page")] + array (
  'class' => 'page_hello---ABZ3GE root',
) , ["default" => ob_get_clean()]+array_shift($__s)); ?>

<?php // return ob_get_clean();
        // dbg("+++ assetsholder ", $this->is_start, $this->assetholder);
    }

    // public function debug_info(){
    //    return /Users/rw/dev/playground/phuety/docs/src/pages/hello.phue.php ~ ;
    // }
}
