
<?php

$position = isset($props['head']) ? 'head' : 'body';
// print $this->assets->get($position);
// print $props['$asset']->get($position);
dbg("++ run assets", $position, $this->assetholder, " --- ", $this->assetholder->get($position));
print $this->assetholder->get($position);
// var_dump($this->assetholder);
//print '<link rel="rest"></link>';
/* need php end tag here */
?>