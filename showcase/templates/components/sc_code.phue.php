<div>

    <details class="code">
        <summary>{{file}}</summary>

        <pre><code class="">{{code}}</code></pre>
    </details>

</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
    hljs.highlightAll();
</script>
<style>
    details {
        padding: 1em;

    }

    .code,
    code {
        background-color: #f0f0f0;
        color: black;
        /* padding: 0; */
        display: block;
        justify-content: left;
    }

    .hljs {
        background-color: #f0f0f0;
    }

    .code p.label {
        background-color: white;
        display: inline-block;
        width: auto;
        padding: 0 1em;
        margin-top: 0;
        border: 1px solid #666;
        /* border-left: 1px solid #666; */
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
$code = file_get_contents($dir . $props->file);
