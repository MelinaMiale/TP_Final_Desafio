<?php

class RegistrationController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function registroForm() {
        $this->renderer->render("registro");
    }

    public function registrar() {
        $user_name = $_POST["user_name"];
        $password = $_POST["password"];

        $existe = $this->model->getUserByName($user_name);
        if ($existe) {
            $this->renderer->render("registro", ["error" => "El usuario ya existe"]);
        } else {
            $this->model->crearUsuario($user_name, $password);
            header("Location: ?controller=login&method=loginForm");
        }
    }
}
