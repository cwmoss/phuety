<?php

namespace phuety\Test;

use Exception;
use PHPUnit\Framework\TestCase;

use phuety\expression\parser;
use phuety\expression\evaluator;
use phuety\expression\data;

class ExpressionTest extends TestCase {

    public function xtestStrings() {
        $parser = new parser();
        $inp = '" \' \" ` ) ( ] [ } { , "';
        $this->assertEquals(
            " ' \" ` ) ( ] [ } { , ",
            $parser->evaluate($inp)
        );
        $inp = "' \' \" ` ) ( ] [ } { , '";

        $this->assertEquals(
            " ' \" ` ) ( ] [ } { , ",
            $parser->evaluate($inp)
        );

        $this->assertEquals(
            " ' \" ` ) ( ] [ } { , ",
            $parser->evaluate($inp)
        );

        $inp = "` ' \" \` ) ( ] [ } { , `";
        $this->assertEquals(
            " ' \" ` ) ( ] [ } { , ",
            $parser->evaluate($inp)

        );
    }

    public function testConcat() {
        $parser = new parser();
        $data = [
            'text' => 'this is some string',
        ];

        $this->assertEquals('foobarbaz', $parser->evaluate('"foo" ~ \'bar\' ~ `baz`', $data));
        $this->assertEquals('foobarbaz', $parser->evaluate('"foo"~\'bar\'~`baz`', $data));

        $this->assertEquals('foo3baz', $parser->evaluate('"foo" ~ 1 + 2 ~ `baz`', $data));
        $this->assertEquals('foo3baz', $parser->evaluate('"foo"~1+2~`baz`', $data));

        $this->assertEquals('message: this is some string', $parser->evaluate('"message: " ~ text', $data));
        $this->assertEquals('message: this is some string', $parser->evaluate('"message: "~text', $data));
        $this->assertEquals('message: this is some string', $parser->evaluate("'message: ' ~ text", $data));
        $this->assertEquals('message: this is some string', $parser->evaluate("'message: '~text", $data));
        $this->assertEquals('message: this is some string', $parser->evaluate('`message: ` ~ text', $data));
        $this->assertEquals('message: this is some string', $parser->evaluate('`message: `~text', $data));
    }
}
