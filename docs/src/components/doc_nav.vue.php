<nav>
    <details v-for="section, sid in chapters.index"
        :open="sid==current_section?'open':''" :class="{active: sid==current_section}">
        <summary>{{ section.title }}</summary>
        <a v-for="chapter in section.c" :href="helper.path(chapter)"
            :class="{active: chapter._file.path == current.path}">{{chapter.title}}</a>
    </details>
</nav>

<?php
//$chapters = $query('chapter() order(_file.path)');
debug_js("current", $props["current"]);
$chapters = $helper["get"]('chapter_index');
// var_dump($chapters, $helper["path"](["_id" => "02-syntax/01-concepts"]));
//die();
$current_section = $current['dir'] ? basename($current['dir']) : basename($chapters[0]['_file']['dir']);

debug_js('chapters', $chapters);

// var_dump($current);
?>