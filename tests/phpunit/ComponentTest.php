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

    private function render_string(string $template, array $data) {
        $runner = new phuety(__DIR__ . '/../fixtures', [], '', ['css' => 'scoped_simple']);
        return $runner->run_template_string($template, $data);
    }

    private function create_and_render(string $template, array $data, array $methods = []) {
        $runner = new phuety(__DIR__ . '/../fixtures', ['hello' => 'hello'], "", ['css' => 'scoped_simple']);
        return $runner->run_get($template, $data);
    }
}
