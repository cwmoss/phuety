<?php

namespace phuety\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use phuety\compiler;
use phuety\component;
use phuety\phuety;

class TemplateTest extends TestCase {

    public function testTemplateString() {
        $result = $this->render_string('<div>hello {{props.name}}</div>', ['name' => 'world']);

        $this->assertSame('<div>hello world</div>', $result);
    }

    public function testIf() {
        $result = $this->render_string('<div :if="props.name">hello {{props.name}}</div>', ['name' => 'world']);
        $this->assertSame('<div>hello world</div>', $result);

        $result = $this->render_string('<template. :if="props.name">hello {{props.name}}</template.>', ['name' => 'world']);
        $this->assertSame('hello world', $result);
    }

    public function testElse() {
        $result = $this->render_string('<div :if="name">hello {{props.name}}</div><div :else>hi stranger</div>', ['name' => '']);
        $this->assertSame('<div>hi stranger</div>', $result);
    }

    public function testElseIf() {
        $result = $this->render_string('<div :if="hour<11">Morning!</div><div :elseif="hour < 17">Good Afternoon</div><div :else>Good Evening</div>', ['hour' => '7']);
        $this->assertSame('<div>Morning!</div>', $result);

        $result = $this->render_string('<div :if="hour<11">Morning!</div><div :elseif="hour < 17">Good Afternoon</div><div :else>Good Evening</div>', ['hour' => '15']);
        $this->assertSame('<div>Good Afternoon</div>', $result);

        $result = $this->render_string('<div :if="hour<11">Morning!</div><div :elseif="hour < 17">Good Afternoon</div><div :else>Good Evening</div>', ['hour' => '20']);
        $this->assertSame('<div>Good Evening</div>', $result);
    }

    public function testFor() {
        $result = $this->render_string('<div :foreach="item in props.items"><em>{{item}}</em></div>', ['items' => ['hello', 'world']]);
        $this->assertSame('<div><em>hello</em></div><div><em>world</em></div>', $result);

        $result = $this->render_string('<template. :foreach="item in props.items"><em>{{item}}</em>!</template.>', ['items' => ['hello', 'world']]);
        $this->assertSame('<em>hello</em>!<em>world</em>!', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div>', ['exists' => true, 'items' => ['hello', 'world']]);
        $this->assertSame('<div><em>hello</em></div><div><em>world</em></div>', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div>', ['exists' => false, 'items' => ['hello', 'world']]);
        $this->assertSame('', $result);
    }

    public function testRaw() {
        $result = $this->render_string('<div :html="html"></div>', ['html' => '<em>hello world</em>']);
        $this->assertSame('<div><em>hello world</em></div>', $result);

        $result = $this->render_string('<template. :foreach="item in props.items"><em>{{item}}</em>!</template.>', ['items' => ['hello', 'world']]);
        $this->assertSame('<em>hello</em>!<em>world</em>!', $result);
    }

    public function testText() {
        $result = $this->render_string('I am <em :if="bread">happy</em><em :else>sad</em>!', ['bread' => true]);
        $this->assertSame('I am <em>happy</em>!', $result);

        $result = $this->render_string('I am <em :if="bread">happy</em><em :else>sad</em>!', ['bread' => false]);
        $this->assertSame('I am <em>sad</em>!', $result);
    }

    public function testComment() {
        $result = $this->render_string('<!-- as first element --><div><!-- wrapper start --></div>', []);
        $this->assertSame('<!-- as first element --><div><!-- wrapper start --></div>', $result);

        $result = $this->render_string('<!-- <div></div> -->', []);
        $this->assertSame('<!-- <div></div> -->', $result);
    }

    private function render_string(string $template, array $data) {
        $runner = new phuety(__DIR__ . '/../fixtures', [], '', ['css' => 'scoped_simple']);
        return $runner->run_template_string($template, $data);
    }

    private function create_and_render(string $template, array $data, array $methods = []) {
        $runner = new phuety(__DIR__ . '/../fixtures', ['hello' => 'hello'], "", ['css' => 'scoped_simple']);
        return $runner->run_get($template, $data);
    }
}
