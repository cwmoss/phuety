<?php

namespace phuety;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Parser;

use Symfony\Component\ExpressionLanguage\Compiler;


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
        $this->compiler ??= new Compiler($this->functions);

        return $this->compiler->reset();
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