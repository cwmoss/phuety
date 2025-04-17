<?php

$position = isset($props->head) ? 'head' : 'body';
dbg("++ run assets", $position, $this->assetholder, " --- ", $this->assetholder->get($position));
print $this->assetholder->get($position);
// var_dump($this->assetholder->get($position));
// die();
/* need php end tag here */
