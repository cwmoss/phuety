<?php

namespace phuety;

class asset {

    public array $assets = [];

    public function push($uid, $asset) {
        $id = match (true) {
            $asset[0] == 'script' && isset($asset[2]['src']) => $asset[2]['src'],
            $asset[0] == 'link' => $asset[2]['href'],
            default => $uid
        };
        if (!isset($this->assets[$id])) {
            $this->assets[$id] = $asset;
        }
    }

    public function get($position = null) {
        return join("\n", array_map(function ($a) {
            // html tag
            return $a[3];
        }, array_filter($this->assets, function ($a) use ($position) {
            return $a[1] == $position;
        })));
    }
}
