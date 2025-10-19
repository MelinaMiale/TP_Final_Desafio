<?php

class RegistrationController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function registroForm() {
        $this->renderer->render("registration");
    }

    public function registrar() {
        $nombre_completo = $_POST["nombreCompleto"];
        $anio_nacimiento = $_POST["anioNacimiento"];
        $sexo = $_POST["sexo"];
        $nombre_usuario = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $ubicacion = $_POST["ubicacion"];
        // foto después

        // Luego llamar al modelo con TODOS los datos
        $this->model->crearUsuario($nombre_completo, $anio_nacimiento, $sexo, $nombre_usuario, $email, $password, $ubicacion);
        
        // Redirigir al login después de registrar
        header("Location: index.php?controller=login&method=loginForm");
        exit;
    }
}
