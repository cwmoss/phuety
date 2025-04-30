<?php

namespace phuety;

class phuety_context {

    public function __construct(
        public string $mode = "prod",
        public string $component = "",
        public string $parent = "",
        public string $top = ""
    ) {
    }

    public function with(string $parent, string $component): self {
        $context = clone ($this);
        $context->parent = $parent;
        $context->component = $component;
        return $context;
    }

    public function with_top(string $top): self {
        $context = clone ($this);
        $context->top = $top;
        $context->parent = "";
        $context->component = $top;
        return $context;
    }
}
