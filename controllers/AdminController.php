<?php

class AdminController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function displayAdmin() {
        if (!isset($_SESSION["user_name"])) {
            header("Location: ?controller=login&method=loginForm");
            exit;
        }

        $username = $_SESSION["user_name"];

        $datos = $this->model->getAdminStats();
        $datos['admin_name'] = $username;
        $this->renderer->render("admin", $datos);
    }

    public function displayAdminUsers() {
        if (!isset($_SESSION["user_name"])) {
            header("Location: ?controller=login&method=loginForm");
            exit;
        }

        $username = $_SESSION["user_name"];
        $datos = $this->model->getAdminStats();
        $datos['admin_name'] = $username;

        $this->renderer->render("admin2", $datos);
    }

}