<div>

    <section class="code">
        <p class="label"><em>{{file}}</em></p>
        <pre><code class="">{{code}}</code></pre>
    </section>

</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
    hljs.highlightAll();
</script>
<style>
    .code {
        background-color: #fafafa;
        color: black;
        padding: 0;
        display: block;
        justify-content: left;
    }

    .hljs {
        background-color: #fafafa;
    }

    .code p.label {
        background-color: white;
        display: inline-block;
        width: auto;
    }

    pre {
        margin: 0;
        padding: 0;
        padding: 1em;
        width: 100%;
    }

    root {
        margin-top: 3em;
    }
</style>
<?php
$dir = __DIR__ . '/../templates/';
$code = file_get_contents($dir . $props['file']);
