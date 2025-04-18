<?php
require __DIR__ . '/../vendor/autoload.php';

function dbg(...$args) {
    error_log(json_encode($args, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 4);
}

$phuety = new phuety\phuety(
    __DIR__ . '/templates',
    [],
    __DIR__ . '/tmp',
    assets_base: "/../public/assets"
);

$data = [
    "name" => "Maggie"
];
$t1 = '<h1 :html="name"></h1>

';

$phuety->run_template_string($t1, $data);
