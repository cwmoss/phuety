<app.layout title="Contact Us" path="/forms">

    <sc.navigation subnav="forms" :path="props.path"></sc.navigation>
    <h1>You can contact us via this form -- <span v-html="hello()"></span></h1>

    <section :if="success">
        <aside>
            <h4>Thank you for your message!</h4>
        </aside>
    </section>

    <form.alpine :else :action="props.path">

        <form.field name="name" label="Name" :value="input.name" :error="errors.name"></form.field>
        <form.field name="email" label="eMail Address" :value="input.email" :error="errors.email"></form.field>
        <form.field name="found_via" label="How Do You Know Us" :value="input.found_via" :error="errors.found_via" type="select" :options="form.via"></form.field>

        <template x-if="isopen">
            <div>Sending...</div>
        </template>

        <span class="vrules" x-ref="vrules" :data-rules="js_rules"></span>

        <button type="submit">Send</button>

    </form.alpine>
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

$rules = [
    'email' => [
        'required',
        'email'
    ],
    'name' => [
        'required',
    ]
];
$messages = [
    'email' => ['required' => 'We need your email address', 'email' => 'This is not a valid email address'],
    'nickname' => ['required' => 'Because we are your friends, we need to know your nickname']
];
$js_rules = json_encode(['r' => $rules, 'm' => $messages]);

$hello = function () {
    return  "hi i am a " . $_SERVER['REQUEST_METHOD'];
};
