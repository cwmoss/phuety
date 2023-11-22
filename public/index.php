<?php
require __DIR__ . '/../vendor/autoload.php';


$phuety = new phuety\phuety(__DIR__ . '/../templates');

send_nocache();

if ($_SERVER['REQUEST_URI'] == '/mvp.css') {
    header("Content-Type: text/css");
    print file_get_contents(__DIR__ . '/../templates/mvp.css');
} elseif ($_SERVER['REQUEST_URI'] == '/components.css') {
    header("Content-Type: text/css");
    foreach (glob(__DIR__ . '/../compiled/*.css') as $f) {
        print file_get_contents($f);
    }
} else {

    $doc = $phuety->run('hello', ['data' => 'huhu']);
    print $doc;
}
