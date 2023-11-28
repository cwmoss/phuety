<layout title="Contact Us" :path="props.path">

    <h1>You can contact us via this form</h1>

    <section v-if="success">
        <aside>
            <h4>Thank you for your message!</h4>
        </aside>
    </section>

    <form-alpine v-else action="/contact2">

        <form-field name="name" label="Name" :value="input.name" :error="errors.name"></form-field>
        <form-field name="email" label="eMail Address" :value="input.email" :error="errors.email"></form-field>
        <form-field name="found_via" label="How Do You Know Us" :value="input.found_via" :error="errors.found_via" type="select" :options="via"></form-field>

        <template x-if="isopen">
            <div>Sending...</div>
        </template>

        <span class="vrules" x-ref="vrules" :data-rules="js_rules"></span>

        <button type="submit">Send</button>

    </form-alpine>
</layout>

<style>
    h1 {
        color: gold;
    }
</style>



<?php
$rules = [
    'email' => [
        'required', 'email'
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
$time = fn () => time();
$success = $props['success'] ?? false;
$fields = ['email', 'name', 'found_via'];
$input = array_reduce($fields, fn ($res, $field) => $res + [$field => $_POST[$field] ?? ""], []);


$validation = [
    'email' => [
        fn ($val) => trim($val) ? '' : 'please enter your email',
        fn ($val) => preg_match("/@/", $val) ? '' : "this is not an email address",
    ],
    'name' => [fn ($val) => trim($val) ? '' : 'please enter your name']
];

$errors = array_map(fn ($field) => "", $input);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    foreach ($validation as $name => $rules) {
        foreach ($rules as $rule) {
            $msg = $rule($input[$name]);
            if ($msg) {
                $errors[$name] = $msg;
                break;
            }
        }
    }
    if (!trim(join("", $errors))) {
        redirect('/contact2?success=1');
    }
}

$via = [
    'friends',
    'family',
    'ads',
];

?>