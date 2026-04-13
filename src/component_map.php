<?php

namespace phuety;

use Closure;

class component_map {

    private array $map = [];

    public function __construct(array $map = [], private string $default = "") {
        foreach ($map as $k => $v) {
            $this->add($k, $v);
        }
    }

    public function add(string $prefix, string|Closure $path) {
        if ($prefix == "*") {
            $this->default = $path;
            return;
        }
        if ($path instanceof Closure) {
            $this->map[$prefix] = $path;
            return;
        }
        if (!str_ends_with($prefix, "*")) {
            $this->map[$prefix] = $path;
            return;
        }
        $swallow = false;
        if (!str_ends_with($path, '/')) {
            $swallow = true;
            $path = rtrim($path, "*");
        }
        if ($path == "/") $path = "./";
        $this->map[$prefix] = [$path, $swallow];
    }

    public function resolve(string $tagname, string $separator = "."): string|Closure {
        // dbg("resolve tag", $tagname, $this->map);
        if (isset($this->map[$tagname])) {
            return $this->map[$tagname];
        }
        [$prefix, $name] = explode($separator, $tagname) + [1 => null];
        if (!$name) return false;
        $cname = str_replace($separator, '_', $tagname);

        if ($found = $this->map[$prefix . $separator . '*'] ?? null) {
            // dbg("resolve prefix", $prefix, $found);
            if ($found instanceof Closure) return $found;
            // swallow prefix?
            if ($found[1]) {
                $cname = str_replace($separator, '_', $name);
            }
            return $found[0] . $cname;
        }
        // dbg("need default:", $this->default);
        return $this->default . $cname;
    }
}
