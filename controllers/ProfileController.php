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

        $currentUser = $_SESSION['user_name'];
        $username = $_GET['user'] ?? $currentUser;

        $userData = $this->model->getUserByUsername($username);

        if (!$userData) {
            echo "No se encontraron datos del usuario.";
            return;
        }

        $userId = $userData['id'];
        $partidas = $this->model->getPartidasByUser($userId);


        $userData['inicial'] = strtoupper(substr($userData['nombre_usuario'], 0, 1));
        $target = "http://desafio-unlam.infinityfreeapp.com/index.php?controller=profile&method=showProfile&user=" . $username;
        $userData['qr_url'] = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($target);


        $userData['partidas'] = $partidas;
        $baseUrl = "/public/subidos/avatars/";
        $userData['foto_perfil'] = !empty($userData['foto'])
            ? $baseUrl . $userData['foto']
            : null;

        $userData['es_propietario'] = ($currentUser === $username);

        $this->renderer->render("profile", $userData);
    }

    public function actualizarPerfil() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_name'])) {
            header("Location: /index.php?controller=Login&method=loginForm");
            exit;
        }

        $nombre_usuario = $_SESSION['user_name'];
        $ciudad = $_POST['ciudad'];
        $pais = $_POST['pais'];
        $password = $_POST['password'] ?? null;
        $fotoNueva = $this->validateImg();

        $this->model->updatePerfil($nombre_usuario, $ciudad, $pais, $password, $fotoNueva);

        header("Location: /index.php?controller=profile&method=showProfile&user=" . $nombre_usuario);

        exit;
    }

    private function validateImg() {
        if (!isset($_FILES['fotoPerfil']) || $_FILES['fotoPerfil']['error'] !== 0) {
            return null;
        }

        $img = $_FILES['fotoPerfil'];

        if ($img['size'] > 10 * 1024 * 1024) return null;
        $typesallowed = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($img['type'], $typesallowed)) return null;

        $extension = pathinfo($img['name'], PATHINFO_EXTENSION);
        $nameImg = uniqid('avatar_') . '.' . $extension;

        $root = __DIR__ . '/../public/subidos/avatars/' . $nameImg;
        if (!move_uploaded_file($img['tmp_name'], $root)) return null;

        return $nameImg;
    }
}
