<app.layout :title="page.title" :page="page">
    <article>
        <h1>{{ page.title }}</h1>
        <doc.markdown :body="page.mdbody"></doc.markdown>
    </article>

    <aside>
        <h4>ON THIS PAGE</h4>
        <doc.markdown toc :body="page.mdbody"></doc.markdown>
    </aside>
</app.layout>

<style>
    p {
        border: 1px solid black;
    }
</style>

<?php

dbg("... template all props", $props["page"]);
$html = "<em>hi</em>";
// $html = $markdown("**hello**");
