<?php

class GameController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

// metodo con datos hardcodeados para probar la interfaz
//    public function startGame() {
//        $this->renderer->render("displayGame", [
//            'idPregunta' => 1,
//            'enunciado' => '¿Quién pintó La Última Cena?',
//            'opcionA' => 'Leonardo da Vinci',
//            'opcionB' => 'Miguel Ángel',
//            'opcionC' => 'Rafael',
//            'opcionD' => 'Donatello',
//            'nombreCategoria' => 'Arte',
//            'colorCategoria' => 'ff0000',
//            'numeroPregunta' => 1
//        ]);
//    }

    public function startGame()
    {
        $userId = $_SESSION["userId"];

        // creo objeto currentGame con todos los atributos necesarios para la partida
        $currentGame = $this->model->createGame($userId);
        $_SESSION["gameId"] = $currentGame->id;

        // llamar a metodo que genere puntaje aleatorio de bot
        $this->model->generateBotScore($currentGame->id);

        // Obtener preguntas "válidas" para el usuario logueado
        $playableQuestions = $this->model->getPlayableQuestionsForUser($userId);

        // Guardar en sesión $playableQuestions a currentGame y la primera es la preguntaActual (la cual va a ir dinamicamente cambiando)
        // tengo que guardar en sesion $currentGame para poder ir mostrando las preguntas y actualizando: pregunta_actual y la respuesta en respuesta_usuario
        $currentGameData = [
            "id" => $currentGame->id,
            "playableQuestions" => $playableQuestions,
            "preguntaActualIndex" => 0
        ];
        $_SESSION["currentGame"] = $currentGameData;

        // Renderizar primera pregunta
        $preguntaActual = $playableQuestions[0]->getIndividualPlayableQuestion(false); // sin respuesta correcta
        $this->renderer->render("displayGame", $preguntaActual);
    }

    public function getNextQuestion() {
        return $this->model->getNextQuestion();
    }

}