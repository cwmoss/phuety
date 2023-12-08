<?php

namespace showcase;

class contactform {

    function handle() {
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
            // fetch request?
            if ($_SERVER['HTTP_ACCEPT'] == 'application/json') {
                // we want to see the spinner :)
                sleep(2);
                header("Content-Type: application/json");
                if (!trim(join("", $errors))) {
                    $res = ['success' => true];
                } else {
                    $res = ['errors' => array_filter($errors)];
                }
                print json_encode($res);
                exit;
            } else {

                if (!trim(join("", $errors))) {
                    redirect('/contact?success=1');
                }
            }
        }
    }

    function use() {
        $fields = ['email', 'name', 'found_via'];
        $input = array_reduce($fields, fn ($res, $field) => $res + [$field => $_POST[$field] ?? ""], []);
        // print_r($_SERVER);

        $validation = [
            'email' => [
                fn ($val) => trim($val) ? '' : 'please enter your email',
                fn ($val) => preg_match("/@/", $val) ? '' : "this is not an email address",
            ],
            'name' => [fn ($val) => trim($val) ? '' : 'please enter your name']
        ];

        $errors = array_map(fn ($field) => "", $input);

        $via = [
            'friends',
            'family',
            'ads',
        ];
    }
}
