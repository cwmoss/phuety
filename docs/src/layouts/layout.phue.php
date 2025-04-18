<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ title }}</title>
  <link rel="stylesheet" href="/assets/css/accordion.css" type="text/css">
  <link rel="stylesheet" href="/assets/css/prism.css" type="text/css">
  <link rel="stylesheet" href="/assets/css/app.css" type="text/css">
  <doc.assets head></doc.assets>
  <script src="/assets/js/prism.js"></script>
  <script src="/assets/js/app.js"></script>
</head>

<body>

  <header>
    <div class="logo">phuety Docs <a href="https://github.com/cwmoss/phuety">github</a></div>
  </header>



  <main>

    <doc.nav :current_id="page._id" :current="page._file?:[]"></doc.nav>

    <slot.></slot.>

  </main>

  <footer>
    <div class="content">
      &copy; 2025
    </div>
  </footer>

</body>

</html>

<?php
dbg("+++ layout props", $props);

// $partial('nav', ['current_id' => $page['_id'], 'current' => $page['_file'] ?? []])