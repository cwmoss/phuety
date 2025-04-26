<h3>{{level}} {{tree.t}}</h3>
<ul :if="tree.c??false">
    <li :foreach="tree.c as child">
        <test.tree :tree="child" :level="level+1"></test.tree>
    </li>
</ul>
<?php
$level = $props->level ?? 1;
