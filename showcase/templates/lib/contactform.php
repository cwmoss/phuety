<?php

namespace showcase;

class contactform {

    public array $fields = ['email', 'name', 'found_via'];
    public array $via = [
        'friends',
        'family',
        'ads',
    ];

    public function rules(): array {
        return [
            'email' => [
                fn ($val) => trim($val) ? '' : 'please enter your email',
                fn ($val) => preg_match("/@/", $val) ? '' : "this is not an email address",
            ],
            'name' => [fn ($val) => trim($val) ? '' : 'please enter your name']
        ];
    }

    function handle(string $path) {
        $input = array_reduce(
            $this->fields,
            fn ($res, $field) => $res + [$field => $_POST[$field] ?? ""],
            []
        );
        $errors = array_map(fn ($field) => "", $input);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            foreach ($this->rules() as $name => $rules) {
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
                    redirect("$path?success=1");
                }
            }
        }
        return [$input, $errors];
    }
}
