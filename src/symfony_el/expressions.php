<?php

namespace phuety\symfony_el;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Parser;

use Symfony\Component\ExpressionLanguage\Compiler;
use Symfony\Component\ExpressionLanguage\Node\FunctionNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;
use Symfony\Component\ExpressionLanguage\Node\Node;

/*

alternative: override classes via composer autoload psr-4:

    "Symfony\\Component\\ExpressionLanguage\\Node\\": "src/override/"

*/

class expressions extends ExpressionLanguage {

    protected Compiler $compiler;

    public function for_phuety(string $expression) {
        $exp = ($this->parse($expression, [], Parser::IGNORE_UNKNOWN_FUNCTIONS | Parser::IGNORE_UNKNOWN_VARIABLES)); // displays (1 + 2)
        //var_dump($exp);
        // var_dump($exp->getNodes()->compile());
        return $this->getCompiler()->compile($exp->getNodes())->getSource();
    }

    private function getCompiler(): Compiler {
        $this->compiler ??= new ex_compiler($this->functions);
        // $this->compiler ??= new Compiler($this->functions);

        return $this->compiler->reset();
    }
}
