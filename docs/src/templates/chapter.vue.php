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

<?php

dbg("... template all props", $props["markdown"]("__hhuhu__"));
$html = "<em>hi</em>";
// $html = $markdown("**hello**");
