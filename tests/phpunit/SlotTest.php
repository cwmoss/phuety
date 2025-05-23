<?php

namespace phuety\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use phuety\compiler;
use phuety\component;
use phuety\phuety;

class SlotTest extends TestCase {

    public function testSimple() {
        $result = $this->create_and_render('page.slots');
        $this->assertSame("<div>hello\n    again</div>", trim($result));

        $result = $this->render_string('<test.slot1>again</test.slot1>');
        $this->assertSame("<div>hello\n    again</div>", trim($result));
    }

    public function testDefault() {
        $result = $this->render_string('<test.slot1></test.slot1>');
        $this->assertSame("<div>hello\n    </div>", trim($result));

        $result = $this->render_string('<test.slot2></test.slot2>');
        $this->assertSame("<div>hello\n    world</div>", trim($result));

        $result = $this->render_string('<test.slot2>again</test.slot2>');
        $this->assertSame("<div>hello\n    again</div>", trim($result));

        $result = $this->render_string('<test.slot2><template. :html="name"></template.></test.slot2>', ["name" => "<strong>waltraud</strong>"]);
        $this->assertSame("<div>hello\n    <strong>waltraud</strong></div>", trim($result));

        $result = $this->render_string('<test.slot2 :html="name"></test.slot2>', ["name" => "<strong>waltraud</strong>"]);
        $this->assertSame("<div>hello\n    <strong>waltraud</strong></div>", trim($result));

        $result = $this->render_string('<test.slotdefault></test.slotdefault>');
        $this->assertSame("<div>hello\n    <em>stranger</em></div>", trim($result));

        $result = $this->render_string('<test.slotdefault :known="true"></test.slotdefault>');
        $this->assertSame("<div>hello\n    <em>my friend</em></div>", trim($result));

        $result = $this->render_string('<test.slotdefault>world</test.slotdefault>');
        $this->assertSame("<div>hello\n    world</div>", trim($result));
    }

    public function testMulti() {
        $result = $this->render_string('<test.slotmulti>achim <h2>good morning</h2><p>bye</bye></test.slotmulti>');
        # $this->assertSame("<div>\n            <h1>welcome</h1>\n        achim <h2>good morning</h2><p>bye</p></div>", trim($result));
        $this->assertSame("<div>            <h1>welcome</h1>\n        achim <h2>good morning</h2><p>bye</p></div>", trim($result));

        $result = $this->render_string('<test.slotmulti>achim <h2 :slot="title">good morning</h2><p>bye</bye></test.slotmulti>');
        $this->assertSame(
            "<div>    <h2>good morning</h2>    achim <p>bye</p></div>",
            trim($result)
        );
    }

    public function testComponentInSlot() {
        $result = $this->render_string('<test.slot1>and <test.slot2>good bye</test.slot2></test.slot1>');
        $this->assertSame("<div>hello\n    and <div>hello\n    good bye</div></div>", trim($result));
    }

    private function render_string(string $template, array $data = []) {
        $runner = new phuety(__DIR__ . '/../fixtures', ['test.*' => '*', 'page.*' => 'pages/*'], '', ['css' => 'scoped_simple']);
        return $runner->render_template_string($template, $data);
    }

    private function create_and_render(string $template, array $data = [], array $methods = []) {
        $runner = new phuety(__DIR__ . '/../fixtures', ['test.*' => '*', 'page.*' => 'pages/*'], "", ['css' => 'scoped_simple']);
        return $runner->render($template, $data);
    }
}
