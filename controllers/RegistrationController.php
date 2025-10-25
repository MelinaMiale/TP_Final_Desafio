<?php

class RegistrationController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function registerForm() {
        $this->renderer->render("registration");
    }

    public function register() {
        $nombre_completo = $_POST["nombreCompleto"];
        $anio_nacimiento = $_POST["anioNacimiento"];
        $sexo = $_POST["sexo"];
        $nombre_usuario = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $ciudad = $_POST["ciudad"];
        $pais = $_POST["pais"];
        // todo: agregar lógica para la foto de usuario
        $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);

        $confirmationCode = $this->model->createUser($nombre_completo, $anio_nacimiento, $sexo, $nombre_usuario, $cleanEmail, $password, $ciudad, $pais);;

        $mailManager = new MailManager();
        $mailManager->sendEmailConfirmation($cleanEmail, $confirmationCode);

        $success = $mailManager->sendEmailConfirmation($cleanEmail, $confirmationCode);
        if (!$success) {
            echo "<p>Error al enviar el correo de confirmación.</p>";
        }

        $this->renderer->render("partial/successful_registration");
        exit;
    }

    public function validateNewUser() {
        $confirmationCode = $_GET['confirmationCode'] ?? null;

        if ($confirmationCode && $this->model->validateUser($confirmationCode)) {
            $this->renderer->render("partial/successful_account_validation");
        } else {
            $this->renderer->render("partial/failed_account_validation");
        }
    }
}
