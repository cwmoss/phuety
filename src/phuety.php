<?php

namespace phuety;

use Le\SMPLang\SMPLang;

class phuety {


    public compiler $compiler;
    public SMPLang $expression_parser;

    public array $compiled = [];

    public string $component_name_separator = ".";

    public function __construct(public string $base, public array $map = [], public string $cbase = "", public array $opts = ['css' => 'scope']) {
        if (!$cbase) $this->cbase = $base . '/../compiled';
        if (!$map) {
            $this->map = ['layout' => 'layout'];
        }
        dbg("start");
        $this->compiler = new compiler($this);
        $this->expression_parser = new SMPLang(['strrev' => 'strrev']);
    }

    public function asset_base(): string {
        return $this->base . '/../public/assets';
    }
    public function run_template_string(string $tpl, array $data) {
        $component = component::new_from_string($tpl, $this->cbase);
        var_dump($component);
        $component->engine = $this;
        $component->ep = $this->expression_parser;
        $component->assetholder = new asset;
        return $component->run($data);
    }

    public function run(string $cname, array $data) {
        $component = $this->get_component($cname, true);
        $data['$asset'] = new asset;
        return $component->run($data);
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

tagname form-field
cname form_field

#        rule-key    rule-value     component source file (.vue.php)
location form-*   => form/       => form/form_field 

tagname page-hello
cname page_hello
location page-* => pages/* => pages/hello

tagname phuety-assets
cname phuety-assets
location phuety-* => * => assets

tagname layout
cname layout
location layout => layout => layout
*/
    public function get_component_source_location($tagname) {
        if (isset($this->map[$tagname])) {
            return $this->map[$tagname];
        }
        [$prefix, $name] = explode($this->component_name_separator, $tagname) + [1 => null];
        if (!$name) return false;
        if (isset($this->map[$prefix . $this->component_name_separator . '*'])) {
            $cname = str_replace($this->component_name_separator, '_', $tagname);
            $path = $this->map[$prefix . $this->component_name_separator . '*'];
            if (str_ends_with($path, '/')) {
                $path .= $cname;
            } else {
                $path = str_replace('*', str_replace($this->component_name_separator, '_', $name), $path);
            }
            return $path;
        }
        return false;
    }

    public function get_component_source($tagname) {
        $path = $this->get_component_source_location($tagname);

        return file_get_contents($this->base . '/' . $path . '.vue.php');
    }

    public function get_component($tagname, $start = false): component {
        $cname = str_replace($this->component_name_separator, '_', $tagname);
        if ($this->compiled[$cname] ?? null) {
            $comp = $this->compiled[$cname];
        } else {
            $uid = $this->compiler->compile($cname, $this->get_component_source($tagname));
            $comp = $this->load_component($cname);
        }
        if ($start) $comp->is_start = true;
        return $comp;
    }



    public function load_component($name) {
        // $cname = str_replace('-', '_', $name); //  . '_component';
        $comp = component::load_class($name, $this->cbase);
        $comp->engine = $this;
        $comp->ep = $this->expression_parser;
        if (!current($this->compiled)) {
            $comp->assetholder = new asset;
        } else {
            $comp->assetholder = current($this->compiled)->assetholder;
        }
        $this->compiled[$name] = $comp;
        return $comp;
    }
}
