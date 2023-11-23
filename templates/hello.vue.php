<p-layout :title="title">

  <p-qrcode data="i love you"></p-qrcode>

  <p-userprofile userid="favuser" size="3"></p-userprofile>

  <h2>hello <span>{{ strrev(name) }}</span></h2>

  <p-userprofile dark v-if="ok" userid="bad"></p-userprofile>

  <p-userprofile dark v-if="ok" :userid="user"></p-userprofile>
</p-layout>

<style>
  h2 {
    color: magenta;
  }

  @media (width >=600px) {
    span {
      font-size: 2rem;
    }
  }
</style>

<?php


$title = "startseite!";
$name = "welt";
$ok = true;
$user = '1234';
?>