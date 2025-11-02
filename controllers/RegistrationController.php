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
    private function validateImg(){

        if (!isset($_FILES['fotoPerfil']) || $_FILES['fotoPerfil']['error'] !== 0) {
            return null;
        }

        $img = $_FILES["fotoPerfil"];

        if ($img['size'] > 10 * 1024 * 1024) {
            echo "Imagen muy pesada. Máximo 10MB.";
            return null;
        }

        $typesallowed = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($img['type'], $typesallowed)) {
            echo "Tipo de archivo no permitido. Solo JPG, JPEG y PNG.";
            return null;
        }

        $extension = pathinfo($img['name'], PATHINFO_EXTENSION);
        $nameImg = uniqid('avatar_') . '.' . $extension;

        $root = __DIR__ . '/../public/subidos/avatars/' . $nameImg;
        if (!move_uploaded_file($img['tmp_name'], $root)) {
            echo "Error al guardar la imagen.";
            return null;
        }

        return $nameImg;

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
        $nombreFoto = $this->validateImg();

        $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);

        $confirmationCode = $this->model->createUser($nombre_completo, $anio_nacimiento, $sexo, $nombre_usuario, $cleanEmail, $password, $ciudad, $pais, $nombreFoto);;

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
