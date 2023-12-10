<layout :title="title" :path="props.path">

  <sc-qrcode data="i love you"></sc-qrcode>

  <h2 v-if="ok">hello {{ strrev(name) }}</h2>

  <sc-modal title="Content In Modal For Some Reasons" open="Can I Have Modals?">
    <p>Why? But yeah.</p>
  </sc-modal>

  <sc-code file="pages/home.vue.php"></sc-code>

  <sc-code file="components/sc_qrcode.vue.php"></sc-code>

  <sc-code file="layout.vue.php"></sc-code>

</layout>


<?php
$title = "Homepage!";
$name = "welt";
$ok = true;
