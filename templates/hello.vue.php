<p-layout :title="title">

  <p-qrcode data="i love you"></p-qrcode>

  <p-userprofile userid="favuser"></p-userprofile>

  <h2>hello {{ strrev(name) }}</h2>



  <p-userprofile dark v-if="ok" :userid="user"></p-userprofile>
</p-layout>

<style>
  h2 {
    color: magenta;
  }
</style>

<?php


$title = "startseite!";
$name = "welt";
$ok = true;
$user = '1234';
?>