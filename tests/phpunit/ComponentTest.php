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

    $this->assertSame('<div class="hello root">hello world</div>', trim($result));
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
    $runner = new phuety(__DIR__ . '/../fixtures', ['hello' => 'hello', 'test.*' => '*'], '', ['css' => 'scoped_simple']);
    return $runner->render_template_string($template, $data);
  }

  private function create_and_render(string $template, array $data, array $methods = []) {
    $runner = new phuety(__DIR__ . '/../fixtures', ['hello' => 'hello', 'test.*' => '*'], "", ['css' => 'scoped_simple']);
    return $runner->render($template, $data);
  }
}
