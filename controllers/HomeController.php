<?php

class HomeController {
    private $model;
    private $renderer;
    /* necesito un modelo con los siguientes datos:
          * el nombre del usuario, que ya está guardado en la sesión.
          * el historial de partidas
          * (FALTA) cantidad de preguntas correctas de esa partida
          * mi posición en el ranking?
    */

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function displayHome() {
        if (!isset($_SESSION["user_name"])) {
            header("Location: ?controller=login&method=loginForm");
            exit;
        }

        $username = $_SESSION["user_name"];
        $datos = $this->model->getUserStats($username);

        $this->renderer->render("home", $datos);
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