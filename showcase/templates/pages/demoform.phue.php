<app.layout title="Contact Us" path="/forms">

  <sc.navigation subnav="forms" :path="props.path"></sc.navigation>

  <h1>You can contact us via this form...</h1>


  <section :if="success">
    <aside>
      <h4>Thank you for your messagecc!</h4>
    </aside>
  </section>

  <form :else :action="props.path" method="POST">
    <form.field name="name" label="Name" :value="input.name" :error="errors.name"></form.field>
    <form.field name="email" label="eMail Address" :value="input.email" :error="errors.email"></form.field>
    <form.field name="found_via" label="How Do You Know Us" :value="input.found_via" :error="errors.found_via" type="select" :options="form.via"></form.field>
    <button type="submit">Send</button>
  </form>

  <p>end</p>

</app.layout>

<style>
  h1 {
    color: gold;
  }
</style>

<?php

use showcase\contactform;

$success = $props->success ?? false;
$form = new contactform;
[$input, $errors] = $form->handle($props->path);
