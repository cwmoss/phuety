<?php

require __DIR__ . '/../vendor/autoload.php';

$compiler = new phuety\compiler(__DIR__ . '/../templates');

$dom = new DOMDocument();
$html = file_get_contents(__DIR__ . '/../templates/layout.vue.php');

$dom->loadHTML($html);

$dom2 = $dom->cloneNode(true);
$dom2->documentElement->childNodes->item(1)->setAttribute("color", "bright");
print $dom2->saveHTML();
print $dom->saveHTML();
print serialize($dom2);
print "memory: " . hum_size(memory_get_usage()) . "\n";

print "\n--\n";
print "create dom with parse\n";
$t1 = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
}
print_r(benchmark_time($t1));
print "memory: " . hum_size(memory_get_usage()) . "\n";

print "\n--\n";
print "create dom with clone\n";
$tpl = new DOMDocument();
@$tpl->loadHTML($html);

$t1 = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $dom = $tpl->cloneNode(true);
}
print_r(benchmark_time($t1));
print "memory: " . hum_size(memory_get_usage()) . "\n";

print "\n--\n";
print "simple include with buffer\n";
$t1 = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    ob_start();
    include __DIR__ . '/../templates/layout.vue.php';
    $res = ob_get_clean();
}
print_r(benchmark_time($t1));
print "memory: " . hum_size(memory_get_usage()) . "\n";

print "peak memory: " . hum_size(memory_get_peak_usage()) . "\n";

function benchmark_time($start) {
    $elapsed = microtime(true) - $start;
    $time = [
        'time' => $elapsed,
        'ms' => (int)($elapsed * 1000),
        'microsec' => (int)($elapsed * 1000 * 1000),
        'print' => null
    ];
    $time['print'] = $time['ms'] ? $time['ms'] . ' ms' : $time['microsec'] . ' μs';
    return $time;
}

function hum_size($size) {
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}
