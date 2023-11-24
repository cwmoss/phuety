<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <phuety-assets header></phuety-assets>
  <link rel="stylesheet" href="/assets/mvp.css">
  <link rel="stylesheet" href="/assets/components.css">
  <title>{{ props.title }}</title>
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

  <header><sc-navigation :path="props.path"></sc-navigation></header>
  <main>
    <h1>it's pure phuety {{smile}}</h1>





    <p-userprofile dark userid="startuser"></p-userprofile>

    <slot></slot>

    <p-userprofile userid="enduser"></p-userprofile>

  </main>
  <phuety-assets></phuety-assets>
</body>

</html>

<?php
$bodyclass = ''; // $props['class'] ?? '';
$smile = "😃";
?>