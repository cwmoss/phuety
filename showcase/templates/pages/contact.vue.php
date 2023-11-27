<layout title="Contact Us" :path="props.path">

    <h1>You can contact us via this form</h1>

    <section v-if="success">
        <h4>Thank you for your message!</h4>
    </section>

    <form v-else action="/contact" method="POST">

        <form-field name="name" label="Name" :value="input.name" :error="errors.name"></form-field>
        <form-field name="email" label="eMail Address" :value="input.email" :error="errors.email"></form-field>
        <form-field name="found_via" label="How Do You Know Us" :value="input.found_via" :error="errors.found_via" type="select" :options="via"></form-field>
        <button type="submit">Send</button>

    </form>
</layout>

<style>
    h1 {
        color: gold;
    }
</style>

<?php

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
        redirect('/contact?success=1');
    }
}

$via = [
    'friends',
    'family',
    'ads',
];

?>