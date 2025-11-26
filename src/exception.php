<?php

namespace phuety;

use Exception as GlobalException;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Throwable;

class exception extends GlobalException {

    public function __construct(
        public string $msg = "an error occured in phuety",
        public int $src_line = 0,
        public ?string $step = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($this->format(), 23);
    }

    static public function new_from_expressionparser(SyntaxError $e, instruction $instruction) {
        // $instruction = self::find_instruction($e);
        // var_dump($e->getTrace());
        return new self($e->getMessage(), $instruction->line_no, "expression for `$instruction->name`\n");
    }

    public function format() {
        $trace = $this->getTrace();
        $context = $this->find_context($trace);
        if ($context) {
            // dbg("line numbers:", $this->src_line, $context->line_offsets);
            if ($this->src_line > $context->line_offsets[0])
                $this->src_line = $this->src_line + ($context->line_offsets[1] - $context->line_offsets[0]);
            $context_line = sprintf(" => %s\n", $this->show_line($context));
        }
        return sprintf(
            "\n[phuety] %s %s\n => line %s %s\n%s",
            $this->step($trace),
            $this->msg,
            $this->src_line,
            $context?->src_file,
            $context_line ?? ""
        );
    }

    public function step($trace) {
        return $this->step ?? $trace[0]["class"];
    }

    public function find_context($trace): ?parts {
        $compile = array_find($trace, fn($line) => $line["function"] == "compile_template");
        if ($compile) {
            return $compile["args"][1];
        }
        return null;
    }

    public function show_line(parts $parts) {
        $lines = explode("\n", $parts->source);
        return $lines[$this->src_line];
    }
}
