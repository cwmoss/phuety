<?php

namespace phuety;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Parser;

use Symfony\Component\ExpressionLanguage\Compiler;
use Symfony\Component\ExpressionLanguage\Node\FunctionNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;
use Symfony\Component\ExpressionLanguage\Node\Node;

/*

override method via composer autoload psr-4:

    "Symfony\\Component\\ExpressionLanguage\\Node\\": "src/override/"

*/

class expressions extends ExpressionLanguage {

    protected Compiler $compiler;

    public function for_phuety(string $expression) {
        $exp = ($this->parse($expression, [], Parser::IGNORE_UNKNOWN_FUNCTIONS | Parser::IGNORE_UNKNOWN_VARIABLES)); // displays (1 + 2)
        //var_dump($exp);
        // var_dump($exp->getNodes()->compile());
        return $this->getCompiler()->compile($exp->getNodes())->getSource();
        $compiler = $this->getCompiler();
        $nodes = $exp->getNodes();
        foreach ($nodes->nodes as $n) {
            dbg("> comp >", get_class($n));
            $n->compile($compiler);
        }
        dbg("> comp end <<", $compiler->getSource());
        return $compiler->getSource();
    }

    private function getCompiler(): Compiler {
        $this->compiler ??= new ex_compiler($this->functions);
        // $this->compiler ??= new Compiler($this->functions);

        return $this->compiler->reset();
    }
}

class ex_compiler extends Compiler {

    public string $source = '';

    public function compile(Node $node): static {
        dbg("> comp0 >", get_class($node));

        // $node->compile($this);
        // return $this;

        $cls = get_class($node);
        if ($cls == NameNode::class) {
            $this->compile_name($node);
        } elseif ($cls == FunctionNode::class) {
            $node->compile($this);
            // $this->compile_function($node);
        } else {
            $node->compile($this);
        }

        dbg("> comp0 end <<", $this->getSource());
        return $this;

        foreach ($node->nodes as $n) {
            dbg("> comp >", get_class($n));
            $n->compile($this);
        }
        dbg("> comp end <<", $this->getSource());
        return $this;
    }

    public function subcompile(Node $node): string {
        $current = $this->source;
        $this->source = '';

        $cls = get_class($node);
        if ($cls == NameNode::class) {
            $this->compile_name($node);
        } elseif ($cls == FunctionNode::class) {
            # $this->compile_function($node);
            $node->compile($this);
        } else {
            $node->compile($this);
        }

        # $node->compile($this);

        $source = $this->source;
        $this->source = $current;

        return $source;
    }

    public function getFunction(string $name): array {
        // return $this->functions[$name];
        return [
            "compiler" => fn(...$args) => \sprintf('$__d->_call("%s")(%s)', $name, implode(', ', $args)),
        ];
    }

    public function compile_name($node) {
        dbg("> comp0 name>", $node->attributes['name']);
        $this->raw('$__d->_get("' . $node->attributes['name'] . '")');
    }

    public function compile_function($node) {
        dbg("> comp0 function>", $node->attributes['name']);
        $arguments = [];
        foreach ($node->nodes['arguments']->nodes as $node) {
            $arguments[] = $this->subcompile($node);
        }

        //  $function = $compiler->getFunction($this->attributes['name']);
        $function = [
            "compiler" => fn(...$args) => \sprintf('$__d->_call("%s")(%s)', $node->attributes['name'], implode(', ', $args)),
        ];

        $this->raw($function['compiler'](...$arguments));
    }

    /**
     * Gets the current PHP code after compilation.
     */
    public function getSource(): string {
        return $this->source;
    }

    /**
     * @return $this
     */
    public function reset(): static {
        $this->source = '';

        return $this;
    }

    /**
     * Adds a raw string to the compiled code.
     *
     * @return $this
     */
    public function raw(string $string): static {
        $this->source .= $string;

        return $this;
    }

    /**
     * Adds a quoted string to the compiled code.
     *
     * @return $this
     */
    public function string(string $value): static {
        $this->source .= \sprintf('"%s"', addcslashes($value, "\0\t\"\$\\"));

        return $this;
    }
}
/*
// $expressionLanguage = new ExpressionLanguage();

$expressionLanguage = new expr;

var_dump($expressionLanguage->evaluate('1 + 2')); // displays 3

$expressionLanguage->for_phuety('{start: props.address.c("100"), end: ende?.date, start: context["xy"].page,
something: else[or.new]
}');
*/