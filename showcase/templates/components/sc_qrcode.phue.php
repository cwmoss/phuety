<img :src="code" :alt="'QR Code..'">

{{props.phuety}}

<?php

use  chillerlan\QRCode\{QRCode, QROptions};

$code = (new QRCode)->render($props->data);
