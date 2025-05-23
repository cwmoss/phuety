<?php

namespace phuety\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use phuety\compiler;
use phuety\component;
use phuety\phuety;

class ComponentTest extends TestCase {

    public function testSimple() {
        $result = $this->create_and_render('test.hello', ['name' => 'world']);

        $this->assertSame('<div class="test_hello root">hello world</div>', trim($result));
    }

    public function testProperties() {
        $names = [(object) ["name" => "Anna"]];

        $result = $this->render_string('<test.firstname title="some title" :person-list="names"></test.firstname>', ['names' => $names]);

        $this->assertSame("<h1>some title</h1>\n<div>Anna</div>", trim($result));
    }

    public function testFallthroughClass() {
        $result = $this->render_string('<test.hello class="blue" name="world"></test.hello>', []);

        $this->assertSame('<div class="test_hello root blue">hello world</div>', trim($result));

        $result = $this->render_string('<test.multiroot class="blue"></test.multiroot>', []);
        $this->assertSame("<h1>hello</h1>\n<div class=\"static\">world</div>", trim($result));
    }

    public function testFallthroughId() {
        $result = $this->render_string('<test.hello id="blue" name="world"></test.hello>', []);

        $this->assertSame('<div class="test_hello root" id="blue">hello world</div>', trim($result));
    }

    public function testRecursiveComponent() {
        $tree = (object) ["t" => "one", "c" => [
            (object) ["t" => "two", "c" => [
                (object) ["t" => "two.1"]
            ]],
            (object) ["t" => "three"]
        ]];
        $result = $this->render_string('<div><h1>tree</h1><test.tree :tree="tree"></test.tree></div>', ['tree' => $tree]);

        $this->assertXmlStringEqualsXmlString('<div><h1>tree</h1>
<h3>1 one</h3>
<ul>
  <li>
    <h3>2 two</h3>
    <ul>
      <li>
        <h3>3 two.1</h3>
      </li>
    </ul>
  </li>
  <li>
    <h3>2 three</h3>
  </li>
</ul></div>', trim($result));
    }

    private function render_string(string $template, array $data) {
        $runner = new phuety(__DIR__ . '/../fixtures', ['test.*' => './*'], '', ['css' => 'scoped_simple']);
        return $runner->render_template_string($template, $data);
    }

    private function create_and_render(string $template, array $data, array $methods = []) {
        $runner = new phuety(__DIR__ . '/../fixtures', ['test.*' => './*'], "", ['css' => 'scoped_simple']);
        return $runner->render($template, $data);
    }
}
