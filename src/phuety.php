<?php

namespace phuety;

class phuety {


    public compiler $compiler;
    public array $compiled = [];


    public function __construct(public string $base, public array $map = [], public string $cbase = "", public array $opts = ['css' => 'scope']) {
        if (!$cbase) $this->cbase = $base . '/../compiled';
        if (!$map) {
            $this->map = ['layout' => 'layout'];
        }
        $this->compiler = new compiler($this);
    }

    public function run_template_string(string $tpl, array $data) {
        $component = component::new_from_string($tpl, $this->cbase);
        $component->engine = $this;
        $component->assetholder = new asset;
        return $component->start_running($data);
    }

    public function run(string $cname, array $data) {
        $component = $this->get_component($cname);
        $data['$asset'] = new asset;
        return $component->start_running($data);
    }

    public function is_component($tagname) {
        if ($this->get_component_source_location($tagname) === false) {
            return false;
        }
        return true;
    }
    /*

tagname form-field
cname form_field
location form-* => form/ => form/form_field 

tagname page-hello
cname page_hello
location page-* => pages/* => pages/hello


*/
    public function get_component_source_location($tagname) {
        if (isset($this->map[$tagname])) {
            return $this->map[$tagname];
        }
        [$prefix, $name] = explode('-', $tagname) + [1 => null];
        if (!$name) return false;
        if (isset($this->map[$prefix . '-*'])) {
            $cname = str_replace('-', '_', $tagname);
            $path = $this->map[$prefix . '-*'];
            if (str_ends_with($path, '/')) {
                $path .= $cname;
            } else {
                $path = str_replace('*', str_replace('-', '_', $name), $path);
            }
            return $path;
        }
        return false;
    }

    public function get_component_source($tagname) {
        $path = $this->get_component_source_location($tagname);

        return file_get_contents($this->base . '/' . $path . '.vue.php');
    }

    public function get_component($tagname): component {
        $cname = str_replace('-', '_', $tagname);
        if ($this->compiled[$cname] ?? null) {
            $comp = $this->compiled[$cname];
        } else {
            $uid = $this->compiler->compile($cname, $this->get_component_source($tagname));
            $comp = $this->load_component($cname);
        }
        return $comp;
    }



    public function load_component($name) {
        // $cname = str_replace('-', '_', $name); //  . '_component';
        $comp = component::load_class($name, $this->cbase);
        $comp->engine = $this;
        if (!current($this->compiled)) {
            $comp->assetholder = new asset;
        } else {
            $comp->assetholder = current($this->compiled)->assetholder;
        }
        $this->compiled[$name] = $comp;
        return $comp;
    }
}
