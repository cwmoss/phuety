<?php
require __DIR__ . '/../vendor/autoload.php';

$compiler = new phuety\compiler(__DIR__ . '/../templates');

if ($_SERVER['REQUEST_URI'] == '/mvp.css') {
    header("Content-Type: text/css");
    print file_get_contents(__DIR__ . '/../templates/mvp.css');
} elseif ($_SERVER['REQUEST_URI'] == '/components.css') {
    header("Content-Type: text/css");
    foreach (glob(__DIR__ . '/../compiled/*.css') as $f) {
        print file_get_contents($f);
    }
} else {


    $c = $compiler->get_component('hello');

    $doc = $c->start_running(['data' => 'huhu']);
    print $doc;
}
