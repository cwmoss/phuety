<img :src="code" :alt="'QR Code..'">
<em v-html="props.data"></em>


<?php

use  chillerlan\QRCode\{QRCode, QROptions};

$code = (new QRCode)->render($props['data']);
