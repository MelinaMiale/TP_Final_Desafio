<?php

class LoginController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function loginForm() {
        $this->renderer->render("login", []); //SIN EL [] APARECE : Warning: Trying to access array offset on value of type null i C:\xampp\htdocs\TP_Final_Desafio\vendor\mustache\src\Mustache\Parser.php on line 278
    }

    public function login() {
        $user_name = $_POST["user_name"];
        $password = $_POST["password"];
        $resultado = $this->model->getUserWith($user_name, $password);

        if ($resultado) {
            $_SESSION["user_name"] = $user_name;
            header("Location: ?controller=login&method=home");
            exit;
        } else {
            $this->renderer->render("login", [
                "error" => "Usuario o clave incorrecta"
            ]);
        }
    }

    public function home() {
        if (!isset($_SESSION["user_name"])) {
            header("Location: ?controller=login&method=loginForm");
            exit;
        }

        $this->renderer->render("home", ["user_name" => $_SESSION["user_name"]]);
    }

    public function logout() {
        session_destroy();
        header("Location: ?controller=login&method=loginForm");
    }
}
