<?php
//dbg("... all props", $props);
// $markdown = $helper["markdown"];

if (isset($props->toc)) print $helper->markdown_toc($props->body);
else print $helper->markdown($props->body);
// print $props['body'];
