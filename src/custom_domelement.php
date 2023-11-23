<?php

namespace phuety;

use DOMElement;

class custom_domelement extends DOMElement {

    public ?props $data;

    public function hey($d = null) {
        static $data = "";
        if ($d) $data = $d;
        print "jo $data!\n";
    }
}
