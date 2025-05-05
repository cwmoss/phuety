<?php

namespace phuety\Test;

use Exception;
use music_index;
use PHPUnit\Framework\TestCase;
use phuety\compiler;
use phuety\component;
use phuety\phuety;

class MapTest extends TestCase {

    public function testEmptyMap() {
        $result = $this->render_string('<foo.hello :name="name"></foo.hello>', [], ["name" => "world"]);

        $this->assertSame('<div>hello world</div>', trim($result));
    }

    public function testPrefixedPathIsBasePath() {
        $result = $this->create_and_render('test.hello', ["test.*" => "./*"], ['name' => 'world']);
        $this->assertSame('<div class="test_hello root">hello world</div>', trim($result));

        $result = $this->create_and_render('test.hello', ["test.*" => "*"], ['name' => 'world']);
        $this->assertSame('<div class="test_hello root">hello world</div>', trim($result));

        $result = $this->create_and_render('test.hello', ["test.*" => "/*"], ['name' => 'world']);
        $this->assertSame('<div class="test_hello root">hello world</div>', trim($result));
    }

    public function testDefaultPath() {
        $result = $this->create_and_render('bar.test1', ["*" => "diverse/"], ['name' => 'world']);
        $this->assertSame('<h1>bar_test1</h1>', trim($result));
    }

    public function testFixedName() {
        $result = $this->create_and_render('barx.test', ["barx.test" => "hello"], ['name' => 'world']);
        $this->assertSame('<div class="barx_test root">hello world</div>', trim($result));
    }

    public function testNoSFC() {
        $result = $this->create_and_render('bary.hello', ["bary.*" => __DIR__ . "/../fixtures/components/*"], ['name' => 'world']);
        $this->assertSame('<div>hi world</div>', trim($result));
    }

    public function testLoader() {
        $result = $this->create_and_render('music.index', ["music.*" => function ($tag) {
            require_once(__DIR__ . "/../fixtures/render_components/music_index.php");
            return new \my\music\app\music_index;
        }], ['category' => 'trance']);
        $this->assertSame('<div>index of trance</div>', trim($result));
    }

    private function render_string(string $template, array $map = [], array $data = []) {
        $compiled = __DIR__ . "/../compiled";
        `rm -rf $compiled`;
        $runner = new phuety(__DIR__ . '/../fixtures', $map, '', ['css' => 'scoped_simple']);
        return $runner->render_template_string($template, $data);
    }

    private function create_and_render(string $template, array $map = [], array $data = [], array $methods = []) {
        $compiled = __DIR__ . "/../compiled";
        `rm -rf $compiled`;
        $runner = new phuety(__DIR__ . '/../fixtures', $map, "", ['css' => 'scoped_simple']);
        return $runner->render($template, $data);
    }
}
