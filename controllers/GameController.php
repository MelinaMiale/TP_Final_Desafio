<?php

class GameController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    /*
    public function startGame() {
    // partida:
        // creo objeto currentGame con todos los atributos necesarios para la partida
        $currentGame = $this->model->createGame();
        $_SESSION["gameId"] = $currentGame->id;
        // llamar a metodo que genere puntaje aleatorio de bot
        $this->model->generateBotScore();
    // preguntas:
        // creo un array del objeto anterior y pedirle al modelo que lo inicialice
        $playableQuestions = $this->model->getPlayableQuestionsForUser();

        // asigno $playableQuestions a currentGame y la primera es la preguntaActual (la cual va a ir dinamicamente cambiando)

        $this->renderer->render("displayGame", $currentGame, $playableQuestions);
    }
    */

    public function startGame() {
        $this->renderer->render("displayGame", [
            'idPregunta' => 1,
            'enunciado' => '¿Quién pintó La Última Cena?',
            'opcionA' => 'Leonardo da Vinci',
            'opcionB' => 'Miguel Ángel',
            'opcionC' => 'Rafael',
            'opcionD' => 'Donatello',
            'nombreCategoria' => 'Arte',
            'colorCategoria' => 'ff0000',
            'numeroPregunta' => 1
        ]);
    }

}