<layout.default :title="page.title" :page="page">
    <article>
        <h1>{{ page.title }}</h1>
        <sft.markdown :body="page.mdbody"></sft.markdown>
    </article>

    <aside>
        <h4>ON THIS PAGE</h4>
        <sft.markdown toc :body="page.mdbody"></sft.markdown>
    </aside>
</layout.default>

<style>
    p {
        border: 1px solid black;
    }
</style>

<?php

dbg("... template all props", $props->page);
$html = "<em>hi</em>";
// $html = $markdown("**hello**");
