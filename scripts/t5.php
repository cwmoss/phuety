<?php

$render = new render;

ob_start();
print $render->render("hello");
print $render->defer_render("head");
print $render->render("body");
print $render->defer_render("end-body");
print $render->render("footer");
$out = ob_get_clean();
print $render->post_process($out);

class render {

    public $total = 0;
    public $buffer = [];

    function render($string) {
        $this->total++;
        return $string . " ($this->total) \n";
    }

    function defer_render($string) {
        $key = md5($string);
        $this->buffer[$key] = fn () => $this->render($string);
        return $key;
    }

    function post_process($output) {
        print "\n--\n$output\n--\n";
        print_r($this->buffer);
        $repl = array_map(fn ($buf) => $buf(), $this->buffer);
        print_r($repl);
        $output = str_replace(array_keys($this->buffer), $repl, $output);
        return $output;
    }
}
