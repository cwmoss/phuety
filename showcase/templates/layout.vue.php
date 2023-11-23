<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
  <link rel="stylesheet" href="/assets/mvp.css">
  <link rel="stylesheet" href="/assets/components.css">
  <title>{{ props.title }}</title>
</head>

<body :class="bodyclass">

  <header><sc-navigation :path="props.path"></sc-navigation></header>
  <main>
    <h1>it's pure phuety {{smile}}</h1>

    <article>



      <p-userprofile dark userid="startuser"></p-userprofile>

      <slot></slot>

      <p-userprofile userid="enduser"></p-userprofile>
    </article>
  </main>
</body>

</html>

<?php
$bodyclass = ''; // $props['class'] ?? '';
$smile = "😃";
?>