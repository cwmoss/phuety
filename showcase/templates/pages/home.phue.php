<app.layout :title="title" :path="props.path">

  <p :html="title"></p>

  <sc.qrcode data="i love you"></sc.qrcode>

  <h2 :if="ok">hello {{ strrev(name) }}</h2>


  <sc.modal title="Content In Modal For Some Reasons" open="Can I Have Modals?">
    <p>Why? But yeah.</p>

    <em>ok</em>

  </sc.modal>

  <img html="dummy">

  <sc.code file="components/sc_modal.phue.php"></sc.code>
  <sc.code file="pages/home.phue.php"></sc.code>

  <sc.code file="components/sc_qrcode.phue.php"></sc.code>

  <sc.code file="layout.phue.php"></sc.code>

  <p>{{not_settet_variable??"this is not set"}}</p>

  <sc.page.navigation total_pages="7" :current_page="page"></sc.page.navigation>

  <ul>
    <li :foreach="items() as it">{{it.rev}}</li>
  </ul>

  <ul>
    <li :foreach="items2(list) as it">{{it.orig}}</li>
  </ul>

  <ul>
    <li :foreach="items3 as it">{{it.orig}}</li>
  </ul>

  <div :if="news" :foreach="news as new">{{new}}</div>
  <em :else>no news</em>

</app.layout>

<script>
  console.log("hey")
</script>
<?php
$title = "Homepage!";
$name = "welt";
$ok = true;
$page = $_GET["page"] ?? 1;

$list = ["apple", "cucumber"];

$items = function () use ($list) {
  foreach ($list as $it) {
    yield ["orig" => $it, "rev" => strrev($it)];
  }
};

$items2 = function ($arr) {
  foreach ($arr as $it) {
    yield ["orig" => $it, "rev" => strrev($it)];
  }
};

$items3 = array_map(
  fn($it) => ["orig" => $it, "rev" => strrev($it)],
  $list
);
