<app.layout :title="title" :path="props.path">

  <sc.qrcode data="i love you"></sc.qrcode>

  <h2 :if="ok">hello {{ strrev(name) }}</h2>

  <sc.modal title="Content In Modal For Some Reasons" open="Can I Have Modals?">
    <p>Why? But yeah.</p>

    <em>ok</em>

  </sc.modal>

  <sc.code file="components/sc_modal.phue.php"></sc.code>
  <sc.code file="pages/home.phue.php"></sc.code>

  <sc.code file="components/sc_qrcode.phue.php"></sc.code>

  <sc.code file="layout.phue.php"></sc.code>

</app.layout>

<script>
  console.log("hey")
</script>
<?php
$title = "Homepage!";
$name = "welt";
$ok = true;
