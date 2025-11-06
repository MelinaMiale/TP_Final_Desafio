<?php

class GameController
{
    private $model;
    private $renderer;

    public function __construct($model, $renderer)
    {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function getPlayableQuestionsAndSetGameData($userId, $currentGameId) {
        $playableQuestions = $this->model->getPlayableQuestionsForUser($userId);

        if (empty($playableQuestions)) {
            $this->resetUserProgressIfNoQuestions();
        }

        if (!isset($_SESSION["currentGame"]["questionDisplayNumber"])) {
            $_SESSION["currentGame"]["questionDisplayNumber"] = 1;
        }

        // aca voy a setear el numero de preg que. seria el questionNumber que muestro en la vista
        if (!isset($_SESSION["currentGame"]["questionDisplayNumber"])) {
            $_SESSION["currentGame"]["questionDisplayNumber"] = 1;
        }

        $score = $_SESSION["currentGame"]["score"] ?? 0;
        $currentGameData = [
            "gameId" => $currentGameId,
            "playableQuestions" => $playableQuestions,
            "currentQuestionIndex" => 0,
            "score" => $score,
            "questionDisplayNumber" => $_SESSION["currentGame"]["questionDisplayNumber"],
            "activeQuestion" => [
                "id" => $playableQuestions[0]->questionId,
                "timestamp" => time()
            ]
        ];
        $_SESSION["currentGame"] = $currentGameData;

        $activeQuestion = $playableQuestions[0]->getIndividualPlayableQuestion(false);
        $activeQuestion['questionNumber'] = $_SESSION["currentGame"]["questionDisplayNumber"];
        $this->renderer->render("displayGame", $activeQuestion);
    }

    public function startGame()
    {
        $userId = $_SESSION["userId"];

        // creo objeto currentGame con todos los atributos necesarios para la partida
        $currentGame = $this->model->createGame($userId);
        $this->getPlayableQuestionsAndSetGameData($userId, $currentGame->id);
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
            $this->renderWrongAnswer($submittedAnswer);
        } else { // si es correcta...
            // 1. acumular el puntaje en score,
            $_SESSION["currentGame"]["score"] = $_SESSION["currentGame"]["score"] + 1;
            $_SESSION["currentGame"]["questionDisplayNumber"] += 1;
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

    private function renderWrongAnswer($submittedAnswer) {
        $index = $_SESSION["currentGame"]["currentQuestionIndex"];
        $payedQuestion = $_SESSION["currentGame"]["playableQuestions"][$index];
        $completePayedQuestion = $payedQuestion->getIndividualPlayableQuestion(true);
        $flags = [
            "isUserA" => $submittedAnswer === "A",
            "isUserB" => $submittedAnswer === "B",
            "isUserC" => $submittedAnswer === "C",
            "isUserD" => $submittedAnswer === "D",
            "isCorrectA" => $completePayedQuestion['correctAnswer'] === "A",
            "isCorrectB" => $completePayedQuestion['correctAnswer'] === "B",
            "isCorrectC" => $completePayedQuestion['correctAnswer'] === "C",
            "isCorrectD" => $completePayedQuestion['correctAnswer'] === "D",
        ];
        $this->renderer->render("wrongAnswer", array_merge($completePayedQuestion, $flags, [
            "respuesta_usuario" => $submittedAnswer,
            "questionId" => $completePayedQuestion['questionId'],
            "yaReportada" => isset($_SESSION["reportedQuestionId"])
        ]));
        exit;
    }

    private function isSameQuestion($questionId) {
        $activeQuestionId = $_SESSION['currentGame']['activeQuestion']['id'];
        return $activeQuestionId == $questionId;
    }

    private function isCorrectAnswer($submittedAnswer) {
        $currentIndex = $_SESSION['currentGame']['currentQuestionIndex'];
        $question = $_SESSION['currentGame']['playableQuestions'][$currentIndex];
        $correctAnswer = $question->correctAnswer;
        return ($submittedAnswer === $correctAnswer);
    }

    private function updateQuestionRatio($questionId, $wasCorrect) {
        $this->model->saveResponseRelatedData($questionId, $wasCorrect);
    }

    private function updateUserResponse($submittedAnswer, $questionId, $wasCorrect) {
        $this->model->updateUserResponseData($submittedAnswer, $questionId, $wasCorrect);
    }

    private function storeResults() {
        $this->model->storeGameResults();
    }

    private function endGame($submittedAnswer) {
        $activeQuestionId = $_SESSION['currentGame']['activeQuestion']['id'];
        // actualizar el ratio de la preg (tabla pregunta)
        $this->updateQuestionRatio($activeQuestionId, false);
        // guardar resp de usuario (tabla usuario)
        $this->updateUserResponse($submittedAnswer, $activeQuestionId, false);
        // guardar puntaje jugador/es (tabla partida), por ahora solo un jugador
        $this->storeResults();
    }

    public function getAndDisplayNextQuestion($submittedAnswer) {
        // el indez ya lo aumenté en submitAnswer
        $index = $_SESSION['currentGame']['currentQuestionIndex'];
        $playableQuestions = $_SESSION['currentGame']['playableQuestions'];
        // si me quede sin preguntas busco mas.
        if (!isset($playableQuestions[$index])) {
            $currentGameId = $_SESSION["gameId"];
            $userId = $_SESSION["userId"];
            $this->getPlayableQuestionsAndSetGameData($currentGameId, $userId);
        }
        $activeQuestion = $playableQuestions[$index]->getIndividualPlayableQuestion(false);
        $_SESSION['currentGame']["activeQuestion"]['id'] = $activeQuestion['questionId'];
        $_SESSION['currentGame']['activeQuestion']['time'] = time();
        $this->renderer->render("displayGame", $activeQuestion);
    }

    public function resetUserProgressIfNoQuestions() {
        $this->model->resetUserQuestionHistory();
        $this->renderer->render("noMoreQuestions");
        exit;
    }
}