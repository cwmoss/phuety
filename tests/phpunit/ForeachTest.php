<?php

namespace phuety\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use phuety\compiler;
use phuety\component;
use phuety\phuety;
use ArrayIterator;

class ForeachTest extends TestCase {

    private function render_string(string $template, array $data) {
        $runner = new phuety(__DIR__ . '/../fixtures', [], '', ['css' => 'scoped_simple']);
        return $runner->render_template_string($template, $data);
    }

    public function testGenerator() {
        $t = ['hello', 'world'];
        $gen = function () use ($t) {
            foreach ($t as $it) {
                yield $it;
            }
        };
        $result = $this->render_string('<div :foreach="item in props.items"><em>{{item}}</em></div>', ['items' => $gen()]);
        $this->assertSame('<div><em>hello</em></div><div><em>world</em></div>', $result);

        $result = $this->render_string('<template. :foreach="item in props.items"><em>{{item}}</em>!</template.>', ['items' => $gen()]);
        $this->assertSame('<em>hello</em>!<em>world</em>!', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div>', ['exists' => true, 'items' => $gen()]);
        $this->assertSame('<div><em>hello</em></div><div><em>world</em></div>', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div>', ['exists' => false, 'items' => $gen()]);
        $this->assertSame('', $result);
    }

    public function testGeneratorElse() {
        $t = [];
        $gen = function () use ($t) {
            foreach ($t as $it) {
                yield $it;
            }
        };
        $result = $this->render_string('<div :foreach="item in props.items"><em>{{item}}</em></div><em :else>nothing</em>', ['items' => $gen()]);
        $this->assertSame('<em>nothing</em>', $result);

        $result = $this->render_string('<template. :foreach="item in props.items"><em>{{item}}</em>!</template.><em :else>nothing</em>', ['items' => $gen()]);
        $this->assertSame('<em>nothing</em>', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div><em :else>x nothing</em>', ['exists' => true, 'items' => $gen()]);
        $this->assertSame('', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div><em :else>x nothing</em>', ['exists' => false, 'items' => $gen()]);
        $this->assertSame('<em>x nothing</em>', $result);
    }

    public function testIterator() {
        // $t = new ArrayIterator(['hello', 'world']);

        $result = $this->render_string('<div :foreach="item in props.items"><em>{{item}}</em></div>', ['items' => new ArrayIterator(['hello', 'world'])]);
        $this->assertSame('<div><em>hello</em></div><div><em>world</em></div>', $result);

        $result = $this->render_string('<template. :foreach="item in props.items"><em>{{item}}</em>!</template.>', ['items' => new ArrayIterator(['hello', 'world'])]);
        $this->assertSame('<em>hello</em>!<em>world</em>!', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div>', ['exists' => true, 'items' => new ArrayIterator(['hello', 'world'])]);
        $this->assertSame('<div><em>hello</em></div><div><em>world</em></div>', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div>', ['exists' => false, 'items' => new ArrayIterator(['hello', 'world'])]);
        $this->assertSame('', $result);
    }

    public function testIteratorElse() {
        $t = [];

        $result = $this->render_string('<div :foreach="item in props.items"><em>{{item}}</em></div><em :else>nothing</em>', ['items' => new ArrayIterator($t)]);
        $this->assertSame('<em>nothing</em>', $result);

        $result = $this->render_string('<template. :foreach="item in props.items"><em>{{item}}</em>!</template.><em :else>nothing</em>', ['items' => new ArrayIterator($t)]);
        $this->assertSame('<em>nothing</em>', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div><em :else>x nothing</em>', ['exists' => true, 'items' => new ArrayIterator($t)]);
        $this->assertSame('', $result);

        $result = $this->render_string('<div :if="exists" :foreach="item in props.items"><em>{{item}}</em></div><em :else>x nothing</em>', ['exists' => false, 'items' => new ArrayIterator($t)]);
        $this->assertSame('<em>x nothing</em>', $result);
    }

    public function testNested() {
        $t = [
            'hello' => ['a', 'b'],
            'world' => ['z']
        ];
        $result = $this->render_string('<div :foreach="props.items as key=>item"><em>{{key}} {{item[0]}}</em><b :foreach="item as char" :html="char"></b></div>', ['items' => $t]);
        $this->assertSame('<div><em>hello a</em><b>a</b><b>b</b></div><div><em>world z</em><b>z</b></div>', $result);
    }
}
