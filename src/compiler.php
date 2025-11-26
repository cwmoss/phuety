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

    public function compile($name, array $src) {
        [$source, $src_file, $is_compiled] = $src;
        if ($is_compiled) return;

        $splitter = new splitter([], $this->engine->asset_base(), $this->custom_tags, $this->engine->opts);
        $parts = $splitter->split_php($source, $name);
        $parts->compile_basedir = $this->cbase;
        $parts->src_file = $src_file;
        $parts->source = $source;
        $is_layout = false;
        $dom = null;
        $head = null;
        if (
            str_starts_with($parts->html, '<html') || str_starts_with($parts->html, '<!DOCTYPE') ||
            str_starts_with($parts->html, '<root') || str_starts_with($parts->html, '<head') || str_starts_with($parts->html, '<x-page')
        ) {
            $is_layout = true;
            // $dom = compiler::get_document($html);
            // $source = str_replace(["<head", "</head>"], ["<xead", "</xead>"], $source);
            if (preg_match("~(<head.*?>)(.*?)(</head>)~ism", $parts->html, $mat, \PREG_OFFSET_CAPTURE)) {
                $parts->line_offsets = [
                    // line where <head.. starts
                    count(explode("\n", substr($parts->html, 0, $mat[0][1]))),
                    // line where </head> ends
                    count(explode("\n", substr($parts->html, 0, $mat[3][1] + 7))),
                ];
                // the head is reduced to the start tag
                $parts->html = str_replace($mat[0][0], $mat[1][0] . '', $parts->html);
                $parts->head = dom::get_template_fragment($mat[2][0]);
                // dbg("++ found head", $parts->html, $mat[0][1], $mat[3][1] + 7, $parts->line_offsets);
            }
            $dom = dom::get_document($parts->html);
            // dbg("++ doctype", $dom->saveHtml($dom->doctype));
        } elseif ($parts->html) {
            $dom = dom::get_template_fragment($parts->html);
        }
        #if ($name == 'assets') {
        #    dom::d("assets-source", $dom);
        #}

        // var_dump($dom);
        $splitter->split_sfc($dom, $name, $is_layout, $parts);

        if ($dom) $this->compile_template($name, $parts);
        $uid = $this->create_component($name, $parts);

        // $uid = component::create($name, $this->cbase, $parts);
        return $uid;
    }

    public function compile_template($name, $parts) {
        $compiler = new template_compiler($parts->dom, [], $this->engine->compiler_options, $parts->head, $parts->total_rootelements);
        $res = $compiler->compile();
        $parts->render = $res;
        $parts->components = $compiler->components;
        return $res;
    }
    public function create_component($name, parts $parts) {
        // dbg("create component", $name, $parts);
        # print "create component $name";
        // print_r($parts);
        $tagname = str_replace('_', '.', $name);
        $tpl = file_get_contents(__DIR__ . '/_component.php');
        $dir = $this->cbase;
        [$php, $use] = $this->get_use_statements($parts->php);
        $components = array_values(array_unique($parts->components));
        if (!$components) $components = null;
        // print_r($parts);
        $repl = [
            'TAGNAME' => $tagname,
            'NAME' => $name,
            'UID' => $parts->uid,
            'ISLAYOUT' => var_export($parts->is_layout, true),
            'PHPCODE' => $php,
            'USESTATEMENTS' => $use,
            'HAS_TEMPLATE' => $parts->html ? 'true' : 'false',
            'HAS_STYLE' => trim($parts->css) ? 'true' : 'false',
            'HAS_CODE' => trim($php) ? 'true' : 'false',
            'ASSETS' => var_export($parts->assets, true),
            'RENDER' => $parts->render,
            'CUSTOM_TAGS' => var_export($parts->custom, true),
            'TOTAL_ROOTELEMENTS' => $parts->total_rootelements,
            'COMPONENTS' => var_export($components, true),
            'DEBUG_INFO' => var_export(["src" => $parts->src_file, "php" => $parts->php_start], true)
        ];

        $tpl = str_replace(array_keys($repl), array_values($repl), $tpl);
        file_put_contents($dir . '/' . $name . '_component.php', $tpl);
        // print "hu";
        // TODO: move to handlers
        if ($repl['HAS_STYLE'] == 'true') {
            //print " style $name";
            file_put_contents($dir . '/' . $name . '.css', $parts->css);
        } else {
            // print "unlink style";
            @unlink($dir . '/' . $name . '.css');
        }
        /*
        if ($repl['HAS_TEMPLATE'] == 'true') {
            $html = sprintf('%s', $parts->dom->saveHTML());
            // file_put_contents($dir . '/' . $name . '.html', $html);
        } else {
            @unlink($dir . '/' . $name . '.html');
        }
*/
        $this->write_js($name, $parts->js);
        // $php = '<?php ' . $parts['php'];
        // file_put_contents($dir . '/' . $name . '.run.php', $php);

        return $parts->uid;
    }

    public function write_js(string $name, array $js) {
        $gendir = $this->engine->asset_build_dir();
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
