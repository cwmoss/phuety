<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

$phuety = new phuety\phuety(__DIR__ . '/templates', [
    'app.layout' => 'layout',
    'page.*' => 'pages/*',
    'form.*' => 'form/',
    'sc.*' => 'components/'
], __DIR__ . '/tmp', compile_mode: "always", assets_base: "/../public/assets");

$phuety->compile_all();
