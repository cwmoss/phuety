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

</app.layout>

<script>
  console.log("hey")
</script>
<?php
$title = "Homepage!";
$name = "welt";
$ok = true;
$page = $_GET["page"] ?? 1;
