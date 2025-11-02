<?php

class ProfileController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function showProfile() {
        if (!isset($_SESSION['user_name'])) {
            header("Location: /index.php?controller=login&method=loginForm");
            exit;
        }

        $username = $_SESSION['user_name'];
        $userData = $this->model->getUserByUsername($username);

        if (!$userData) {
            echo "No se encontraron datos del usuario.";
            return;
        }

        // Traer las partidas del usuario
        $userId = $userData['id'];
        $partidas = $this->model->getPartidasByUser($userId);

        // Datos adicionales
        $userData['inicial'] = strtoupper(substr($userData['nombre_usuario'], 0, 1));
        $userData['qr_base64'] = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://ejemplo.com/perfil/demo";

        // Agregar partidas al contexto de Mustache
        $userData['partidas'] = $partidas;

        // Renderizar la vista
        $this->renderer->render("profile", $userData);
    }
}
