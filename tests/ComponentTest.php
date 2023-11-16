<?php

namespace phuety\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use phuety\compiler;
use phuety\component;

class ComponentTest extends TestCase {

    public function testJustASingleComponent() {
        $result = $this->createAndRender('hello', ['name' => 'world']);

        $this->assertSame('<div class="hello root">hello world</div>', $result);
    }

    private function createAndRender(string $template, array $data, array $methods = []) {
        $c = new compiler(__DIR__ . '/fixtures', ['css' => 'scoped_simple']);
        $tpl = $c->get_component($template);
        $res = $tpl->start_running($data);
        return $res;
    }
}
