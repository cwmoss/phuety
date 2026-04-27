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
 * /Users/rw/dev/playground/phuety/docs/src/layouts/default.phue.php ~ 41
 */

class layout_default_component extends component {
    public string $uid = "layout_default---Hcr/e0";
    public bool $is_layout = true;
    public string $name = "layout_default";
    public string $tagname = "layout.default";
    public bool $has_template = true;
    public bool $has_code = true;
    public bool $has_style = false;
    public array $assets = array (
);
    public array $custom_tags = array (
);
    public int $total_rootelements = 2;
    public ?array $components = array (
  0 => 'phuety.assets',
  1 => 'top.nav',
);

    public function run_code(data_container $props, array $slots, data_container $helper, phuety_context $phuety, asset $assetholder): array{
        // dbg("++ props for component", $this->name, $props);
dbg("+++ layout props", $props);
$prefix = "/phuety";

// $partial('nav', ['current_id' => $page['_id'], 'current' => $page['_file'] ?? []])
        return get_defined_vars();
    }

    public function render($__runner, data_container $__d, array $slots=[]):void {
        // ob_start();
        // if($this->is_layout) print '<!DOCTYPE html>';
        $__s = [];
        ?><!DOCTYPE html><html lang="en"><head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= tag::h($__d->_get("title")) ?></title>
  <?= tag::tag_open_merged_attrs("link", ["href"=> ($__d->_get("prefix") . "/assets/css/accordion.css")], array (
  'rel' => 'stylesheet',
  'type' => 'text/css',
) ) ?>
  <?= tag::tag_open_merged_attrs("link", ["href"=> ($__d->_get("prefix") . "/assets/css/prism.css")], array (
  'rel' => 'stylesheet',
  'type' => 'text/css',
) ) ?>
  <?= tag::tag_open_merged_attrs("link", ["href"=> ($__d->_get("prefix") . "/assets/css/app.css")], array (
  'rel' => 'stylesheet',
  'type' => 'text/css',
) ) ?>
  <?php $__runner($__runner, "phuety.assets", $__d->_get("phuety")->with($this->tagname, "phuety.assets"), [] + array (
  'head' => '',
) ); ?>
  <?= tag::tag_open_merged_attrs("script", ["src"=> ($__d->_get("prefix") . "/assets/js/prism.js")], array (
) ) ?></script>
  <?= tag::tag_open_merged_attrs("script", ["src"=> ($__d->_get("prefix") . "/assets/js/app.js")], array (
) ) ?></script>


</head><body>

  <header>
    <div class="logo">phuety Docs <a href="https://github.com/cwmoss/phuety">github</a></div>
  </header>



  <main>

    <?php $__runner($__runner, "top.nav", $__d->_get("phuety")->with($this->tagname, "top.nav"), ["current_id"=> $__d->_get("page")->_id, "current"=> (($__d->_get("page")->_file) ? ($__d->_get("page")->_file) : ([]))] + array (
) ); ?>

    <?=$slots["default"]??""?>

  </main>

  <footer>
    <div class="content">
      © 2025
    </div>
  </footer>



</body></html><?php // return ob_get_clean();
        // dbg("+++ assetsholder ", $this->is_start, $this->assetholder);
    }

    // public function debug_info(){
    //    return /Users/rw/dev/playground/phuety/docs/src/layouts/default.phue.php ~ 41;
    // }
}
