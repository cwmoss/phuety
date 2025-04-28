<?php

namespace phuety;

use Dom\Element;

class tag {

    public static array $self_closing_tags = [
        "area",
        "base",
        "br",
        "col",
        "embed",
        "hr",
        "img",
        "input",
        "keygen",
        "link",
        "meta",
        "param",
        "source",
        "track",
        "wbr"
    ];

    public static array $boolean_attributes = [
        "allowfullscreen",
        "async",
        "autofocus",
        "autoplay",
        "checked",
        "controls",
        "default",
        "defer",
        "disabled",
        "formnovalidate",
        "inert",
        "ismap",
        "itemscope",
        "loop",
        "multiple",
        "muted",
        "nomodule",
        "novalidate",
        "open",
        "playsinline",
        "readonly",
        "required",
        "reversed",
        "selected",
        "shadowrootclonable",
        "shadowrootdelegatesfocus",
        "shadowrootserializable",
    ];

    public array $class = [];
    public array $wrap = [];
    public array $before = [];
    public array $after = [];

    public bool $is_component = false;
    public bool $is_slot = false;
    public bool $is_asset = false;

    public ?string $slotname = null;

    public function __construct(
        public string $tagname = 'div',
        public string $id = "",
        string|array $class = [],
        public string|array $bindings = [],
        public array $attrs = [],
        public array $data = [],
        public tag|array|string|null $content = null,
        public ?string $html_content_expression = null,
        public ?string $text_content_expression = null,
        public bool $has_children = true,
        string|array $wrap = []
    ) {
        // TODO: dependend on phuety conf
        if (str_starts_with($tagname, "slot.")) {
            $this->is_slot = true;
            [$dummy, $name] = explode(".", $tagname, 2);
            $this->slotname = $name ?: "default";
        } elseif (str_contains($tagname, ".")) {
            $this->is_component = true;
        } elseif ($tagname == "link" && ($attrs["rel"] ?? null) == "assets") {
            $this->is_asset = true;
        }
        if (is_null($content)) {
            $this->content = [];
        } elseif (!is_array($content)) {
            $this->content = [$content];
        }
    }

    public static function new_from_dom_element(Element $el, $bindings_prefixes = [], ?string $html = null): self {
        $tag = [
            "tagname" => $el->localName,
            "has_children" => $el->hasChildNodes(),
            "attrs" => [],
            "bindings" => [],
            "html_content_expression" => $html,
            // "content" => $content,
        ];
        $attributes = dom::attributes($el);
        foreach ($attributes as $name => $value) {
            $is_binding = false;
            foreach ($bindings_prefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $name = str_replace($prefix, '', $name);
                    $is_binding = true;
                    break;
                }
            }
            if ($is_binding) {
                $tag["bindings"][$name] = $value;
            } else {
                $tag["attrs"][$name] = $value;
            }
        }
        return new self(...$tag);
    }

    public function to_attrs(): array {
        $attrs = [];
        if ($this->id) $attrs['id'] = $this->id;
        if ($this->class) $attrs['class'] = join(" ", $this->class);
        return $attrs + $this->attrs + $this->data_to_attrs();
    }

    public function data_to_attrs(): array {
        $data = [];
        foreach ($this->data as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $v = json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            $data['data-' . $k] = $v;
        }
        return $data;
    }

    public function open(): string {
        return self::tag_open($this->tagname, $this->to_attrs());
    }

    public function close(): string {
        return self::tag_close($this->tagname);
    }

    public static function h($attr): string {
        return htmlspecialchars($attr);
    }

    public static function tag_open_merged_attrs(string $name, array $bindings, array $attrs) {
        if ($bindings["class"] ?? null) {
            $class = (array) ($attrs["class"] ?? []);

            foreach ((array) $bindings["class"] as $k => $v) {
                if (is_numeric($k)) {
                    $class[] = $v;
                } else {
                    if ($v) {
                        $class[] = $k;
                    }
                }
            }
            $bindings["class"] = join(" ", $class);
        }
        if ($bindings["style"] ?? null) {
            $style = (array) ($attrs["style"] ?? []);
            if ($style) $style = array_map(fn($s) => rtrim($s, "; ") . ";", $style);

            foreach ((array) $bindings["style"] as $k => $v) {
                if (is_numeric($k)) {
                    $style[] = $v;
                } else {
                    $style[] = sprintf('%s: %s;', self::kebab($k), $v);
                }
            }
            $bindings["style"] = join(" ", $style);
        }
        return self::tag_open($name, $bindings + $attrs);
    }

    public static function tag_open(string $name, array $attrs): string {
        $attr = [];
        foreach ($attrs as $aname => $avalue) {
            if (is_bool($avalue) || in_array($aname, self::$boolean_attributes)) {
                if ($avalue) {
                    $attr[] = $aname;
                }
            } else {
                if (is_array($avalue) || is_object($avalue)) {
                    $avalue = json_encode($avalue, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
                $attr[] = sprintf('%s="%s"', $aname, self::h($avalue));
            }
        }
        /*
        $attrs = array_reduce($attrs, function ($res, $item) {
            if (is_array($item)) {
                $item = sprintf('%s="%s"', $item[0], self::h($item[1]));
            }
            return $res . " " . $item;
        }, "");
        */
        return sprintf('<%s%s%s>', $name, ($attr ? ' ' : ''), join(" ", $attr));
    }

    public static function tag_close(string $name): string {
        if (in_array($name, self::$self_closing_tags)) return "";
        return sprintf('</%s>', $name);
    }

    public static function tag(string $name, array $attrs, string $content = ""): string {
        $start = self::tag_open($name, $attrs);
        return sprintf('%s%s%s', $start, $content, self::tag_close($name));
    }

    public static function kebab(string $camelCase) {
        return preg_replace_callback(
            '/[A-Z]/',
            function ($matches) {
                return '-' . strtolower($matches[0]);
            },
            $camelCase
        );
    }
}
