<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ title }}</title>
  <link rel="stylesheet" :href="prefix~'/assets/css/accordion.css'" type="text/css">
  <link rel="stylesheet" :href="prefix~'/assets/css/prism.css'" type="text/css">
  <link rel="stylesheet" :href="prefix~'/assets/css/app.css'" type="text/css">
  <phuety.assets head></phuety.assets>
  <script :src="prefix~'/assets/js/prism.js'"></script>
  <script :src="prefix~'/assets/js/app.js'"></script>
</head>

<body>

  <header>
    <div class="logo">phuety Docs <a href="https://github.com/cwmoss/phuety">github</a></div>
  </header>



  <main>

    <top.nav :current_id="page._id" :current="page._file?:[]"></top.nav>

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
$prefix = "/phuety";

// $partial('nav', ['current_id' => $page['_id'], 'current' => $page['_file'] ?? []])