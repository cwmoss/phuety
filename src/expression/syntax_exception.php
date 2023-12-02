<?php

namespace phuety\expression;

use Exception;

class syntax_exception extends Exception {
    private $token;
    private $src;

    public function __construct($message, $token, $src) {
        $this->token = $token;
        $this->src = $src;
        parent::__construct($this->make_message($message, $token, $src));
    }

    public function make_message($message, $token, $sourcecode) {
        $msg = "syntax error\n" . $sourcecode . "\n" . str_repeat(' ', ($token->pos - 6)) . '^ ' . $message;
        return $msg;
    }
}
