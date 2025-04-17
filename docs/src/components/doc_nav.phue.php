<nav>
    <details :foreach="section, sid in chapters.index"
        :open="sid==current_section?'open':''"
        :class="{active: sid==current_section}">
        <summary>{{ section.title }}</summary>
        <a :foreach="chapter in section.c" :href="helper.path(chapter)"
            :class="{active: chapter._file.path == current.path}">{{chapter.title}}</a>
    </details>
</nav>

<?php

$chapters = (object) $helper->get('chapter_index');
$current_section = $props->current->dir ? basename($props->current->dir) : basename($chapters[0]->_file->dir);
// dbg("++++ current_section", $current_section, $props);
?>