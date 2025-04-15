<?php

namespace phuety;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use WMDE\VueJsTemplating\Component as vcomponent;

class compiler {
    public array $compiled;
    public string $cbase;
    public array $custom_tags = [];

    public function __construct(public phuety $engine) {
        $this->cbase = $engine->cbase;
    }

    public function set_custom_tag($tag) {
        $this->custom_tags[] = $tag;
    }

    public function compile($name, $source) {
        $splitter = new splitter($this->engine->opts, $this->engine->asset_base());
        [$source, $php] = $splitter->split_php($source);
        $is_layout = false;
        $dom = null;
        $head = null;
        if (
            str_starts_with($source, '<html') || str_starts_with($source, '<!DOCTYPE') ||
            str_starts_with($source, '<root') || str_starts_with($source, '<head') || str_starts_with($source, '<x-page')
        ) {
            $is_layout = true;
            // $dom = compiler::get_document($html);
            // $source = str_replace(["<head", "</head>"], ["<xead", "</xead>"], $source);
            if (preg_match("~(<head.*?>)(.*?)</head>~ism", $source, $mat)) {
                dbg("++ found head", $mat);
                $source = str_replace($mat[0], $mat[1] . '', $source);
                $head = dom::get_template_fragment($mat[2]);
            }
            $dom = dom::get_document($source);
            dbg("++ doctype", $dom->saveHtml($dom->doctype));
        } elseif ($source) {
            $dom = dom::get_template_fragment($source);
        }
        #if ($name == 'assets') {
        #    dom::d("assets-source", $dom);
        #}

        // var_dump($dom);
        $parts = $splitter->split_sfc($dom, $name, $is_layout);
        $php = rtrim($php, '>?');
        $parts['php'] = $php;
        $parts['head'] = $head;
        $parts["render"] = $dom ? $this->compile_dom($name, $parts) : "";
        $uid = $this->create_component($name, $parts);

        // $uid = component::create($name, $this->cbase, $parts);
        return $uid;
    }

    public function compile_dom($name, $parts) {
        $compiler = new dom_compiler($parts["html"], [], $parts["head"]);
        $res = $compiler->compile();
        return $res;
    }
    public function create_component($name, $parts) {
        // dbg("create component", $name, $parts);
        # print "create component $name";
        // print_r($parts);
        $tpl = file_get_contents(__DIR__ . '/_component.php');
        $dir = $this->cbase;
        [$php, $use] = $this->get_use_statements($parts['php']);
        $repl = [
            'NAME' => $name,
            'UID' => $parts['uid'],
            'ISLAYOUT' => $parts['is_layout'] ? 'true' : 'false',
            'PHPCODE' => $php,
            'USESTATEMENTS' => $use,
            'HAS_TEMPLATE' => $parts['html'] ? 'true' : 'false',
            'HAS_STYLE' => trim($parts['css']) ? 'true' : 'false',
            'HAS_CODE' => trim($php) ? 'true' : 'false',
            'ASSETS' => var_export($parts['assets'], true),
            'RENDER' => $parts["render"],
            'CUSTOM_TAGS' => var_export([], true)
        ];

        $tpl = str_replace(array_keys($repl), array_values($repl), $tpl);
        file_put_contents($dir . '/' . $name . '_component.php', $tpl);
        // print "hu";
        if ($repl['HAS_STYLE'] == 'true') {
            //print " style $name";
            $css = sprintf(".%s{\n%s\n}", $parts['uid'], $parts['css']);
            file_put_contents($dir . '/' . $name . '.css', $css);
        } else {
            // print "unlink style";
            @unlink($dir . '/' . $name . '.css');
        }

        if ($repl['HAS_TEMPLATE'] == 'true') {
            $html = sprintf('%s', $parts['html']->saveHTML());
            // file_put_contents($dir . '/' . $name . '.html', $html);
        } else {
            @unlink($dir . '/' . $name . '.html');
        }

        $this->write_js($name, $parts['js']);
        // $php = '<?php ' . $parts['php'];
        // file_put_contents($dir . '/' . $name . '.run.php', $php);

        return $repl['UID'];
    }

    public function write_js(string $name, array $js) {
        $gendir = $this->engine->asset_base() . '/generated';
        foreach (glob("{$gendir}/{$name}---*.js") as $fname) {
            unlink($fname);
        }
        foreach ($js as $fname => $code) {
            file_put_contents($gendir . '/' . $fname, $code);
        }
    }
    public function get_use_statements($code) {
        $use = preg_match_all("/^\s*use\s+[^;]+;\s*$/ms", $code, $mat, \PREG_SET_ORDER);
        if (!$mat) return [$code, ""];

        $use = join("\n", array_map(fn($el) => $el[0], $mat));
        $code = preg_replace("/^\s*use\s+[^;]+;\s*$/ms", "", $code);
        return [$code, $use];
    }
}
