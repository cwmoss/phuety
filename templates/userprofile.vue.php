<aside v-if="show">
  <div>
    <strong>user: {{ backw(props.userid) }}</strong> <em>{{size}}</em>
  </div>
</aside>

<style>
  root {
    padding: 1em;
  }
</style>

<?php

$show = $props['userid'] != 'bad';
$size = $props['size'] ?? 50;

$backw = function ($str) use ($props) {
  return substr(strrev($str), 0, $props['size'] ?? null) . " " . ($props['size'] ?? ' -- ');
};

?>