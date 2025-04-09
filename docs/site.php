<?php

use slowfoot\configuration;
use slowfoot\document;
use slowfoot\store;
use slowfoot\hook;

class site {

    function init() {
        dbg("++ site init ++");
        hook::add_filter('markdown.shortcodes', function ($shortcodes) {
            dbg("++ add markdown shortcodes ++");
            $shortcodes['source'] = $this->show_sourcecode(...);
            return $shortcodes;
        });
    }

    public function show_sourcecode(array $code, ?document $current_obj, configuration $conf): string {
        $file = $code[0];
        $file = get_absolute_path_from_base($file, $current_obj->_file["full"], $conf->base);
        $source = file_get_contents($file);
        $source = htmlspecialchars($source);
        $lang = $code[1];
        if (!$lang) {
            $lang = pathinfo($file, PATHINFO_EXTENSION);
        }
        return sprintf('<pre class="language-%s"><code>%s</code></pre>', $lang, $source);
    }

    static public function load_chapter_index(configuration $config, store $db) {
        $chapters = $db->query('chapter() order(_file.path)');
        //$current_section = $current['dir']?basename($current['dir']):basename($chapters[0]['_file']['dir']);
        $current_section = "";
        $chapters = array_reduce($chapters, function ($res, $chapter) use ($current_section) {
            $sid = basename($chapter['_file']['dir']);
            if (!isset($res[$sid])) {
                $res[$sid] = [
                    'sid' => $sid,
                    'title' => $chapter['chapter_title'] ?? $sid,
                    'active' => $sid == $current_section,
                    'c' => [$chapter]
                ];
            } else {
                $res[$sid]['c'][] = $chapter;
            }
            return $res;
        }, []);
        yield ['_id' => 'chapter_index', 'index' => $chapters];
        return;
    }
}
