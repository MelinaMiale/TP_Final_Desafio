<?php

class PlayerhomeController {
    private $model;
    private $renderer;

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
        $data = $this->model->getUserStats($username);

        $this->renderer->render("home", $data);
    }

    public function switchToEditorRole() {
        $this->model->turnPlayerIntoEditor();
        $this->renderer->render("welcomeEditor");
    }
}