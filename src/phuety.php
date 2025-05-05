<?php

namespace phuety;

use Closure;
use Le\SMPLang\SMPLang;
use phuety\symfony_el\expressions;
use ReflectionClass;
use stdClass;

class phuety {

    public ?compiler $compiler = null;
    public ?expressions $expression_parser = null;

    public array $compiled = [];
    public array $collected = [];

    public string $component_name_separator = ".";

    public string $component_extension = ".php";
    public string $sfc_extension = ".phue.php";

    public component_map $map;

    /** 
     * 
     * @param string $base base directory of template sources
     * @param array $map mapping component names to source files/ directories
     * @param string $cbase base directory of compiled output
     * @param array $opts some options
     * @param string $compile_mode always, compare_timestamps, never 
     * 
     * */
    public function __construct(
        public string $base,
        component_map|array $map = [],
        public string $cbase = "",
        public array $opts = ['css' => 'scope'],
        public string $compile_mode = "always",
        public array $helper = [],
        public string $assets_base = "/assets",
        public ?compiler_options $compiler_options = null,
        public ?phuety_context $context = null
    ) {
        // dbg("start");
        $this->init($map);
    }

    private function init(array|component_map $map) {
        if (is_array($map)) $this->map = new component_map($map);
        else $this->map = $map;
        $this->map->add("phuety.*", __DIR__ . "/components/*");

        if (!$this->cbase) $this->cbase = $this->base . '/../compiled';
        if (!is_dir($this->cbase)) mkdir($this->cbase, recursive: true);
        if ($this->compile_mode == "never") {
            if (!is_readable($this->cbase)) {
                throw new Exception("compile dir must be readable ($this->cbase)");
            }
            if (!$this->context) $this->context = new phuety_context();
        } else {
            if (!is_writable($this->cbase)) {
                throw new Exception("compile dir must be writeable ($this->cbase)");
            }
            if (!is_dir($this->asset_build_dir())) mkdir($this->asset_build_dir(), recursive: true);
            if (!$this->context) $this->context = new phuety_context("dev");
        }
        if ($this->compile_mode !== "never") {
            if (!$this->compiler_options) $this->compiler_options = new compiler_options();
            $this->compiler = new compiler($this);
            $this->expression_parser = new expressions();
            // $this->expression_parser = new dotdata(['strrev' => 'strrev']);
            // $this->expression_parser = new SMPLang(['strrev' => 'strrev']);
        }
    }
    public function set_custom_tag($tag) {
        $this->compiler->set_custom_tag($tag);
    }
    public function set_helper(array $helper) {
        $this->helper = $helper;
    }

    public function asset_base(): string {
        // return $this->base . '/../public/assets';
        return $this->base . $this->assets_base;
    }

    public function asset_build_dir(): string {
        // return $this->base . '/../public/assets';
        return $this->base . $this->assets_base . "/build";
    }

    public function render_template_string(string $tpl, array $data = [], array $helper = [], object $globals = new stdClass): string {
        // dbg("+++data", $data);
        // $component = component::new_from_string($tpl, $this->cbase);
        $cname = "tmp.x" . uniqid();
        $component = $this->get_component($cname, $tpl);
        return $this->render($cname, $data, $helper, $globals);
    }

    public function render(string $cname, array $data = [], array $helper = [], object $globals = new stdClass): string {
        ob_start();
        $this->run($cname, $data, $helper, $globals);
        return ob_get_clean();
    }

    public function run(string $cname, array $data = [], array $helper = [], object $globals = new stdClass) {
        // $this->compiled = [];
        $context = $this->context->with_top($cname);

        $assetholder = $this->collect($cname);
        $data_container = new data_container($globals, $helper);
        $runner = function ($runner, $component_name, phuety_context $context, $props, $slots = []) use ($assetholder, $data_container) {
            $this->get_component($component_name)->run($runner, $this, $context, $data_container->with_props($props), $slots, $assetholder);
        };
        $runner($runner, $cname, $context, $data);
        //$component = $this->get_component($cname, true);
        // $data['$asset'] = new asset;
        // $component->run($this, $data);
        //$component($data, [], new asset);
    }

    public function collect($cname): asset {
        if (isset($this->collected[$cname])) return $this->collected[$cname];
        $all_components = [];
        $this->collect_all($cname, $all_components);
        // dbg("++ all components", $all_components);
        $assetholder = new asset;
        foreach ($all_components as $name => $dummy) {
            $this->get_component($name)->collect_assets($assetholder);
        }
        $this->collected[$cname] = $assetholder;
        return $assetholder;
    }

    public function collect_all($cname, array &$visited) {
        $component = $this->get_component($cname);
        $visited[$cname] = true;
        if ($component->components) {
            foreach ($component->components as $child) {
                if (!isset($visited[$child])) $this->collect_all($child, $visited);
            }
        }
    }

    public function is_component($tagname) {
        return str_contains($tagname, $this->component_name_separator);

        // alternative: look for prefixes
        $tagname = strtolower($tagname);
        if ($this->get_component_source_location($tagname) === false) {
            return false;
        }
        return true;
    }
    /*

tagname form.field
cname form_field

#        rule-key    rule-value     component source file (.phue.php)
location form.*   => form/       => form/form_field 

tagname page.hello
cname page_hello
location page.* => pages/* => pages/hello

tagname app.layout
cname app_layout
location layout => layout => layout
*/
    public function get_component_source_location($tagname) {
        return $this->map->resolve($tagname, $this->component_name_separator);
    }

    public function get_component_name_from_filename($filename, $mapkey, $mapvalue) {
        // single component rule
        if (!str_ends_with($mapkey, "*")) return $mapkey;

        if (str_starts_with($filename, $this->base)) $filename = str_replace($this->base, "", $filename);
        $fname = basename($filename, $this->sfc_extension);
        $tagname = str_replace("_", $this->component_name_separator, $fname);
        $prefix = rtrim($mapkey, ".*");
        $expand = str_ends_with($mapvalue, "*");
        if ($expand) return $prefix . "." . $tagname;
        return $tagname;
    }
    public function get_component_source($tagname): array|callable {
        $path = $this->get_component_source_location($tagname);
        if (!$path) die("could not resolve component source for $tagname");
        if (is_callable($path)) return $path;
        if ($path[0] != "/") $filename = $this->base . '/' . $path;
        else $filename = $path;

        // dbg("++ loading component source", $tagname, $filename, $path);
        if (file_exists($filename . $this->sfc_extension)) {
            return [file_get_contents($filename . $this->sfc_extension), $filename . $this->sfc_extension, false];
        }
        if (file_exists($filename . $this->component_extension)) {
            return [file_get_contents($filename . $this->component_extension), $filename . $this->component_extension, true];
        }
        throw new exception("could not resolve component: $tagname");
    }

    public function get_component($tagname, ?string $string_source = null): render_component|component|Closure { #: component
        if ($this->compiled[$tagname] ?? null) {
            $comp = $this->compiled[$tagname];
        } else {
            $cname = str_replace($this->component_name_separator, '_', $tagname);
            // tmp (string) components
            if ($string_source) {
                $uid = $this->compiler->compile($cname, [$string_source, "", false]);
                $comp = $this->load_component($cname, true);
            } else {
                $source = $this->get_component_source($tagname);
                if (is_callable($source)) {
                    $comp = $source($tagname);
                } else {
                    if ($this->compile_mode != "never") {
                        $uid = $this->compiler->compile($cname, $source);
                    }

                    $comp = $this->load_component($cname);
                }
            }

            $this->compiled[$tagname] = $comp;
        }
        // if ($start) $comp->is_start = true;
        return $comp;
    }

    public function load_component($name, $tmp = false): component|Closure {
        // $cname = str_replace('-', '_', $name); //  . '_component';
        $comp = $this->load_component_class($name, $this->cbase);
        if ($tmp) unlink(new ReflectionClass($comp)->getFileName());
        // $comp->set_engine($this);
        // $comp->set_ep($this->expression_parser);
        if (!current($this->compiled)) {
            //$comp->assetholder = new asset;
        } else {
            //$comp->assetholder = current($this->compiled)->assetholder;
        }
        // $this->compiled[$name] = $comp;
        // return component::get_runner($this, $comp, new asset);
        return $comp;
    }

    public function load_component_class($name, $dir) {
        $cname = "$name" . '_component';
        $classname = "compiled\\$name" . '_component';
        if (file_exists($dir . '/' . $cname . '.php')) {
            require_once($dir . '/' . $cname . '.php');
            $comp = new $classname($dir);
        } else {
            // TODO: make it flexible
            // psr class
            [$prefix, $rest] = explode("_", $name, 2);
            $classname = join('\\', [$prefix, "components", $rest]);
            $comp = new $classname($dir);
        }

        // $comp->load_dom();
        return $comp;
    }

    public function compile(array $components) {
        foreach ($components as $name) {
            // $c = $this->get_component($name);
            $this->run($name, ["path" => "/dummy/path"]);
        }
    }

    public function compile_all(null|string|array $entrypoints = null) {
        $this->compile_mode = "always";
        if ($entrypoints && !is_array($entrypoints)) $entrypoints = [$entrypoints];
        foreach ($this->map as $key => $directory) {
            if ($entrypoints && !in_array($key, $entrypoints)) continue;

            $dir = $directory;
            if ($dir[0] != "/") $dir = $this->base . "/" . $dir;
            $glob = match (true) {
                str_ends_with($dir, "*") => $dir . $this->sfc_extension,
                str_ends_with($dir, "/") => $dir . '*' . $this->sfc_extension,
                default => $dir . $this->sfc_extension
            };
            foreach (glob($glob) as $file) {
                $tagname = $this->get_component_name_from_filename($file, $key, $directory);
                // dbg("find tagname", $tagname);
                $this->get_component($tagname);
            }
            // $this->run($name, ["path" => "/dummy/path"]);
        }
    }
}
