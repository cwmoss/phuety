<?php

namespace phuety\Test;

use Exception;
use music_index;
use PHPUnit\Framework\TestCase;
use phuety\compiler;
use phuety\component;
use phuety\phuety;

class DataTest extends TestCase {

    public function testGlobals() {
        $result = $this->render_string('<foo.hello :name="globals.name"></foo.hello>', [], [], ["name" => "world"]);
        $this->assertSame('<div>hello world</div>', trim($result));

        $result = $this->render_string('<foo.global></foo.global>', [], [], ["name" => "world"]);
        $this->assertSame('<div>hi world</div>', trim($result));
    }

    public function testGlobalsWithRenderComponent() {
        $result = $this->render_string('<music.index :category="cat"></music.index>', ["music.*" => function ($tag) {
            require_once(__DIR__ . "/../fixtures/render_components/music_index.php");
            return new \my\music\app\music_index;
        }], ['cat' => 'trance'], ["user" => "heidi"]);
        $this->assertSame('<div>index of tranceheidi</div>', trim($result));
    }

    private function render_string(string $template, array $map = [], array $data = [], array $globals = []) {
        $compiled = __DIR__ . "/../compiled";
        `rm -rf $compiled`;
        $runner = new phuety(__DIR__ . '/../fixtures', $map, '', ['css' => 'scoped_simple']);
        return $runner->render_template_string($template, $data, globals: (object) $globals);
    }
}
