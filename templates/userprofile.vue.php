<aside v-if="show" class="aside">
  <div>
    <strong>user: {{ backw(props.userid) }}</strong> <em>{{props.size}}</em>
  </div>
</aside>

<style>
  &.aside {
    padding: 1em;
  }
</style>

<?php

$show = $props['userid'] != 'bad';

$backw = function ($str) use ($props) {
  return substr(strrev($str), 0, $props['size'] ?? null) . " " . ($props['size'] ?? ' -- ');
};

?>