<?php

namespace phuety\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use phuety\compiler;
use phuety\component;
use phuety\phuety;

class ComponentTest extends TestCase {

    public function testJustASingleComponent() {
        $result = $this->create_and_render('hello', ['name' => 'world']);

        $this->assertSame('<div class="hello root">hello world</div>', $result);
    }

    public function testTemplateString() {
        $result = $this->render_string('<div>hello {{props.name}}</div>', ['name' => 'world']);

        $this->assertSame('<div>hello world</div>', $result);
    }

    public function testIf() {
        $result = $this->render_string('<div v-if="props.name">hello {{props.name}}</div>', ['name' => 'world']);
        $this->assertSame('<div>hello world</div>', $result);

        $result = $this->render_string('<template v-if="props.name">hello {{props.name}}</template>', ['name' => 'world']);
        $this->assertSame('hello world', $result);
    }

    public function testFor() {
        $result = $this->render_string('<div v-for="item in props.items"><em>{{item}}</em></div>', ['items' => ['hello', 'world']]);
        $this->assertSame('<div><em>hello</em></div><div><em>world</em></div>', $result);

        $result = $this->render_string('<template v-for="item in props.items"><em>{{item}}</em>!</template>', ['items' => ['hello', 'world']]);
        $this->assertSame('<em>hello</em>!<em>world</em>!', $result);

        $result = $this->render_string('<div v-if="exists" v-for="item in props.items"><em>{{item}}</em></div>', ['exists' => true, 'items' => ['hello', 'world']]);
        $this->assertSame('<div><em>hello</em></div><div><em>world</em></div>', $result);

        $result = $this->render_string('<div v-if="exists" v-for="item in props.items"><em>{{item}}</em></div>', ['exists' => false, 'items' => ['hello', 'world']]);
        $this->assertSame('', $result);
    }

    public function testRaw() {
        $result = $this->render_string('<div v-html="html"></div>', ['html' => '<em>hello world</em>']);
        $this->assertSame('<div><em>hello world</em></div>', $result);

        // $result = $this->render_string('<template v-for="item in props.items"><em>{{item}}</em>!</template>', ['items' => ['hello', 'world']]);
        // $this->assertSame('<em>hello</em>!<em>world</em>!', $result);
    }

    private function render_string(string $template, array $data) {
        $runner = new phuety(__DIR__ . '/fixtures', [], '', ['css' => 'scoped_simple']);
        return $runner->run_template_string($template, $data);
    }

    private function create_and_render(string $template, array $data, array $methods = []) {
        $runner = new phuety(__DIR__ . '/fixtures', ['hello' => 'hello'], "", ['css' => 'scoped_simple']);
        return $runner->run($template, $data);
    }
}
