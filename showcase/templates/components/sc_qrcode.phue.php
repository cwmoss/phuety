<img :src="code" :alt="'QR Code for: '~data" :style="{width:size}">


{{phuety}}

<?php

use  chillerlan\QRCode\{QRCode, QROptions};

$code = (new QRCode)->render($props->data);

$size = $props->size ?? "200px";
