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
        $resultado = $this->model->getUserByUserNameAndPassword($user_name, $password);

        if ($resultado && $resultado["cuenta_verificada"] == 1) {
            $_SESSION["user_name"] = $user_name;
            header("Location: ?controller=login&method=home");
            exit;
        } else {
            $mensaje = $resultado
                ? "Tu cuenta aún no fue verificada. Revisa tu correo."
                : "Usuario o clave incorrecta";

            if($mensaje == "Tu cuenta aún no fue verificada. Revisa tu correo."){

            }

            $this->renderer->render("login", [
                // "titulo" => "Iniciar sesión",
                "error" => $mensaje
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
