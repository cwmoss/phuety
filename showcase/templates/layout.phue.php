<!DOCTYPE html>
<html lang="en">

<head class="light">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <app.assets head></app.assets>

  <link rel="stylesheet" href="/assets/mvp.css">
  <link rel="stylesheet" href="/components-css">
  <title :html="props.title"></title>
  <style>
    nav {
      margin-bottom: 1em;
    }

    header {
      padding-bottom: 0;
    }

    main {
      padding-top: 1em;
    }
  </style>
</head>

<body :class="bodyclass">
  <header>
    <sc.navigation :path="props.path"></sc.navigation>
  </header>
  <main>
    <h1>it's pure phuety {{smile}}</h1>
    <p11-userprofile ?dark={a||b} userid="startuser"></p11-userprofile>

    <slot.></slot.>

    <Userprofile userid="enduser"></Userprofile>
  </main>

  <app.assets body />
</body>

</html>

<?php
$bodyclass = ''; // $props['class'] ?? '';
$smile = "😃";

// $head = $this->assetholder->get("head");
// $body = $this->assetholder->get("body");



// print_r($props);
