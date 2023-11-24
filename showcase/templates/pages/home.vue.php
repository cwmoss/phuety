<layout :title="title" :path="props.path">

  <p-qrcode data="i love you"></p-qrcode>

  <p-userprofile userid="favuser" size="3"></p-userprofile>

  <h2>hello {{ strrev(name) }}</h2>

  <p-userprofile dark v-if="ok" userid="bad"></p-userprofile>

  <p-userprofile dark v-if="ok" :userid="user"></p-userprofile>
</layout>


<?php


$title = "startseite!";
$name = "welt";
$ok = true;
$user = '1234';
?>