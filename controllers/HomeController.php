<?php

class HomeController {
    private $model;
    private $renderer;
    /* necesito un modelo con los siguientes datos:
          * el nombre del usuario, que ya está guardado en la sesión.
          * el historial de partidas
          * cantidad de preguntas correctas de esa partida
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

        $datosSimulados = [
            'user_name' => $_SESSION["user_name"],
            'ranking' => 1,
            'score' => 2450,
            'games_played' => 45,
            'games' => [
                ['date' => '11 oct 2023', 'correct' => 8, 'points' => 80],
                ['date' => '10 oct 2023', 'correct' => 9, 'points' => 90],
                ['date' => '9 oct 2023', 'correct' => 10, 'points' => 100],
                ['date' => '8 oct 2023', 'correct' => 7, 'points' => 70],
                ['date' => '7 oct 2023', 'correct' => 9, 'points' => 90],
            ]
        ];

        $this->renderer->render("home", $datosSimulados);
    }

}