<?php
// php -S localhost:4000 showcase/public/index.php
ini_set("error_reporting", E_ALL);
ini_set("display_errors", 0);

$http_method = $_SERVER['REQUEST_METHOD'];
// $path = $_SERVER['REQUEST_URI'] ?: '/';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
error_log("path: $path --");
send_nocache();

if ($_SERVER['REQUEST_URI'] == '/assets/mvp.css') {
    header("Content-Type: text/css");
    print file_get_contents(__DIR__ . '/public/assets/mvp.css');
    exit;
} elseif ($path == '/components-css') {
    header("Content-Type: text/css");
    foreach (glob(__DIR__ . '/tmp/*.css') as $f) {
        print file_get_contents($f);
    }
    exit;
} elseif ($_SERVER['REQUEST_URI'] == '/assets/logo.jpg') {
    header("Content-Type: image/jpeg");
    print file_get_contents(__DIR__ . '/public/assets/logo.jpg');
    exit;
}

// print file_get_contents('php://input');
$the_route = match ("$http_method $path") {
    'GET /' => ['home'],
    'GET /about' => ['about'],
    'GET /forms' => ['forms', $_GET],
    'GET /blog' => ['blog', $_GET],
    'GET /demo-form' => ['demoform', $_GET],
    'GET /demo-webco' => ['demowebco', $_GET],
    'GET /demo-alpine' => ['demoalpine', $_GET],

    'POST /demo-form' => ['demoform', $_POST],
    'POST /demo-webco' => ['demowebco', $_POST],
    'POST /demo-alpine' => ['demoalpine', $_POST],

    // ['POST', '/check_username'] => ['check_username', json_decode(file_get_contents('php://input'), true)],
    default => ['404']
};

$phuety = new phuety\phuety(__DIR__ . '/templates', [
    'app.layout' => 'layout',
    'app.assets' => 'assets',
    'phuety-*' => '*',
    'page.*' => 'pages/*',
    'form.*' => 'form/',
    'sc.*' => 'components/'
], __DIR__ . '/tmp', compile_mode: "never");


print $phuety->run('page.' . $the_route[0], ($the_route[1] ?? []) + ['path' => $path]);


function send_nocache() {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    // header('Content-Type: text/html');
}

function d(...$args) {
    echo '<pre>';
    foreach ($args as $arg) {
        print_r($arg);
    }
    echo '</pre>';
}

function dbg(...$args) {
    error_log(json_encode($args, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 4);
}

function redirect($to) {
    header("Location: $to");
    exit;
}
