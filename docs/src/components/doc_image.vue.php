<?php
$image_tag = $props['image_tag'];

print $image_tag($props["src"], $props["size"], ['alt' => $props["alt"] ?? ""]);
