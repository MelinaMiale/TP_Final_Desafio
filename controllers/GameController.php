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

    public function startGame() {
        $userId = $_SESSION["userId"];

        // creo objeto currentGame con todos los atributos necesarios para la partida
        $currentGame = $this->model->createGame($userId);
        $_SESSION["gameId"] = $currentGame->id;

        // llamar a metodo que genere puntaje aleatorio de bot
        // $this->model->generateBotScore($currentGame->id);

        // Obtener preguntas "válidas" para el usuario logueado
        $playableQuestions = $this->model->getPlayableQuestionsForUser($userId);

        // Guardar en sesión $playableQuestions a currentGame y la primera es la preguntaActual (la cual va a ir dinamicamente cambiando)
        // tengo que guardar en sesion $currentGame para poder ir mostrando las preguntas y actualizando: pregunta_actual y la respuesta en respuesta_usuario
        $currentGameData = [
            "gameId" => $currentGame->id,
            "playableQuestions" => $playableQuestions,
            "currentQuestionIndex" => 0,
            "score" => 0,
            "activeQuestion" => [
                "id" => $playableQuestions[0]->questionId,
                "timestamp" => time()
            ]
        ];
        $_SESSION["currentGame"] = $currentGameData;

        // Renderizar primera pregunta
        $activeQuestion = $playableQuestions[0]->getIndividualPlayableQuestion(false); // sin respuesta correcta
        $this->renderer->render("displayGame", $activeQuestion);
    }

    public function submitAnswer() {
        // si la pregunta que nos responde no es la que le mandamos, #hicisteTrampa, se cierra la partida
        $questionId = $_POST["questionId"];
        $submittedAnswer = $_POST['answer'];
        if (!$this->isSameQuestion($questionId)) {
            $this->endGame($submittedAnswer);
            $this->renderer->render("differentQuestionError"); // mejorar interfaz
            exit;
        }
        // si la respuesta no es correcta muestro error
        if (!$this->isCorrectAnswer($submittedAnswer)) {
            $this->endGame($submittedAnswer);
            $this->renderer->render("wrongAnswer"); // mejorar interfaz
            exit;
        } else { // si es correcta...
            // 1. acumular el puntaje en score,
            $_SESSION["currentGame"]["score"] = $_SESSION["currentGame"]["score"] + 1;
            // 2. actualizar el ratio de aciertos de la preg,
            $this->updateQuestionRatio($questionId, true);
            // 3. guardar resp de usuario,
            $this->updateUserResponse($submittedAnswer, $questionId, true);
            // 4. cambiar el indice de la pregunta activa
            $_SESSION['currentGame']['currentQuestionIndex']++;
            // 5. obtener siguiente pregunta y mandarla a la vista
            $this->getAndDisplayNextQuestion($submittedAnswer);
        }
    }

    function isSameQuestion($questionId) {
        $activeQuestionId = $_SESSION['currentGame']['activeQuestion']['id'];
        return $activeQuestionId == $questionId;
    }

    function isCorrectAnswer($submittedAnswer) {
        $currentIndex = $_SESSION['currentGame']['currentQuestionIndex'];
        $question = $_SESSION['currentGame']['playableQuestions'][$currentIndex];
        $correctAnswer = $question->correctAnswer;
//        echo "<p>respuesta correcta: $correctAnswer. pregunta: {$question->questionId} - {$question->text}</p>";
        return ($submittedAnswer === $correctAnswer);
    }

    function updateQuestionRatio($questionId, $wasCorrect) {
        $this->model->saveResponseRelatedData($questionId, $wasCorrect);
    }

    function updateUserResponse($submittedAnswer, $questionId, $wasCorrect) {
        $this->model->updateUserResponseData($submittedAnswer, $questionId, $wasCorrect);
    }

    function storeResults() {
        $this->model->storeGameResults();
    }

    function endGame($submittedAnswer) {
        $activeQuestionId = $_SESSION['currentGame']['activeQuestion']['id'];
        // actualizar el ratio de la preg (tabla pregunta)
        $this->updateQuestionRatio($activeQuestionId, false);
        // guardar resp de usuario (tabla usuario)
        $this->updateUserResponse($submittedAnswer, $activeQuestionId, false);
        // guardar puntaje jugador/es (tabla partida), por ahora solo un jugador
        $this->storeResults();
    }

    public function getAndDisplayNextQuestion($submittedAnswer) {
        $currentIndex = $_SESSION['currentGame']['currentQuestionIndex'];
        $totalQuestions = count($_SESSION['currentGame']['playableQuestions']);
        if ($currentIndex >= $totalQuestions) {
            // acá llego cuando respondi todas las preguntas
            // guardar puntaje jugador/es (tabla partida), por ahora solo un jugador
            $activeQuestionId = $_SESSION['currentGame']['activeQuestion']['id'];
            // actualizar el ratio de la preg (tabla pregunta)
            $this->updateQuestionRatio($activeQuestionId, true);
            // guardar resp de usuario (tabla usuario)
            $this->updateUserResponse($submittedAnswer, $activeQuestionId, true);
            $this->storeResults();
            $this->renderer->render("victoryScreen"); // hacerrrr
            exit;
        }

        // el indez ya lo estoy aumentando en submitAnswer
        $index = $_SESSION['currentGame']['currentQuestionIndex'];
        $playableQuestions = $_SESSION['currentGame']['playableQuestions'];
        $activeQuestion = $playableQuestions[$index]->getIndividualPlayableQuestion(false);
        $_SESSION['currentGame']["activeQuestion"]['id'] = $activeQuestion['questionId'];
        $_SESSION['currentGame']['activeQuestion']['time'] = time();

        $this->renderer->render("displayGame", $activeQuestion);
    }
}