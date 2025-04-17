<?php

use phuety\phuety;
use slowfoot\template_contract;
use slowfoot\configuration;
use slowfoot\context;

class phuety_adapter implements template_contract {

    public phuety $engine;

    public function __construct(public configuration $config) {
        $this->engine = new phuety($config->src, [
            'app.layout' => 'layouts/layout',
            'app.assets' => 'assets',
            // 'phuety-*' => '*',
            'page.*' => 'pages/*',
            'template.*' => 'templates/*',
            // 'form.*' => 'form/',
            'doc.*' => 'components/'
        ], __DIR__ . "/compiled");
    }
    public function run(string $_template, array $data, array $helper, context $__context): string {
        $name = $_template;
        $cname = $__context->is_page ? "template.{$name}" : "template.{$name}";
        dbg("++ run template", $cname, $name, $helper["markdown"]("*yo**"));
        $this->engine->set_helper($helper);
        ob_start();
        $this->engine->run($cname, $helper + $data);
        return ob_get_clean();
    }

    public function run_page(string $_template, array $data, array $helper, context $__context): string {
        $name = $_template;
        $cname = $__context->is_page ? "page.{$name}" : "page.{$name}";
        dbg("++ run page", $cname, $name, $__context);
        ob_start();
        $this->engine->run($cname, $helper + $data);
        return ob_get_clean();
    }

    public function preprocess($_template, $_base) {
        return [];
    }

    public function remove_tags($content, $tags) {
        return $content;
    }
}
