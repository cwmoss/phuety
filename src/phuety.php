<?php

namespace phuety;

class phuety {

    public string $cbase;
    public compiler $compiler;
    public array $compiled = [];

    public function __construct(public string $base, public array $opts = ['css' => 'scope']) {
        $this->cbase = $base . '/../compiled';
        $this->compiler = new compiler($base, $opts);
    }

    public function run_template_string(string $tpl, array $data) {
        $component = component::new_from_string($tpl, $this->cbase);
        return $component->start_running($data);
    }

    public function run(string $cname, array $data) {
        $component = $this->get_component($cname);
        return $component->start_running($data);
    }

    public function get_component($name): component {
        $cname = $name . '_component';
        if ($this->compiled[$name] ?? null) {
            $comp = $this->compiled[$name];
        } else {
            $uid = $this->compiler->compile($name);
            $comp = $this->load($name);
        }
        return $comp;
    }



    public function load($name) {
        $cname = $name . '_component';
        $comp = component::load($name, $this->cbase);
        $comp->engine = $this;
        $this->compiled[$name] = $comp;
        return $comp;
    }
}
