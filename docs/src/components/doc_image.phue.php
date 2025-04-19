<?php
// $image_tag = $helper->image_tag;

print $helper->image_tag($props->src, $props->size, ['alt' => $props->alt ?? ""]);
