<?php
$image_tag = $helper->image_tag;

print $image_tag($props->src, $props->size, ['alt' => $props->alt ?? ""]);
