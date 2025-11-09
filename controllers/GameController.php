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

        $score = $_SESSION["currentGame"]["score"] ?? 0;
        $currentGameData = [
            "gameId" => $currentGameId,
            "playableQuestions" => $playableQuestions,
            "currentQuestionIndex" => 0,
            "score" => $score,
            "activeQuestion" => [
                "id" => $playableQuestions[0]->questionId,
                "timestamp" => time()
            ]
        ];
        $_SESSION["currentGame"] = $currentGameData;

        $this->model->registerQuestionAssignment($userId, $currentGameData["activeQuestion"]["id"], $currentGameId);

        $activeQuestion = $playableQuestions[0]->getIndividualPlayableQuestion(false);
        $activeQuestion['questionNumber'] = 1;
        $this->renderer->render("displayGame", $activeQuestion);
    }

    public function startGame()
    {
        $userId = $_SESSION["userId"];
        $currentGame = $this->model->createGame($userId);
        $this->getPlayableQuestionsAndSetGameData($userId, $currentGame->id);
    }

    public function submitAnswer() {
        $questionId = $_POST["questionId"];
        $submittedAnswer = isset($_POST['answer']) ? $_POST['answer'] : 'NO_ANSWER';
        $timing = $this->hasTimeRunOut();
        $timeout = $timing["timeout"];
        $elapsedTime = $timing["elapsedTime"];

        if ($timeout) {
            $this->endGame($submittedAnswer);
            $this->renderWrongAnswer($submittedAnswer, true, $elapsedTime);
            exit;
        }

        if (!$this->isSameQuestion($questionId)) {
            $this->endGame($submittedAnswer);
            $this->renderer->render("differentQuestionError"); // mejorar interfaz
            exit;
        }

        if (!$this->isCorrectAnswer($submittedAnswer)) {
            $this->endGame($submittedAnswer);
            $this->renderWrongAnswer($submittedAnswer, false, $elapsedTime);
            exit;
        }

        $_SESSION["currentGame"]["score"] = $_SESSION["currentGame"]["score"] + 1;
        $this->updateQuestionRatio($questionId, true);
        $this->updateUserResponse($submittedAnswer, $questionId, true);
        $_SESSION['currentGame']['currentQuestionIndex']++;
        $this->getAndDisplayNextQuestion($submittedAnswer);

    }

    private function renderWrongAnswer($submittedAnswer, $timeout, $elapsedTime) {
        $index = $_SESSION["currentGame"]["currentQuestionIndex"];
        $payedQuestion = $_SESSION["currentGame"]["playableQuestions"][$index];
        $completePayedQuestion = $payedQuestion->getIndividualPlayableQuestion(true);
        if ($timeout) {
            $flags = [
                "isCorrectA" => $completePayedQuestion['correctAnswer'] === "A",
                "isCorrectB" => $completePayedQuestion['correctAnswer'] === "B",
                "isCorrectC" => $completePayedQuestion['correctAnswer'] === "C",
                "isCorrectD" => $completePayedQuestion['correctAnswer'] === "D",
                "wrongDueToTimeOut" => true
            ];
        } else {
            $flags = [
                "isUserA" => $submittedAnswer === "A",
                "isUserB" => $submittedAnswer === "B",
                "isUserC" => $submittedAnswer === "C",
                "isUserD" => $submittedAnswer === "D",
                "isCorrectA" => $completePayedQuestion['correctAnswer'] === "A",
                "isCorrectB" => $completePayedQuestion['correctAnswer'] === "B",
                "isCorrectC" => $completePayedQuestion['correctAnswer'] === "C",
                "isCorrectD" => $completePayedQuestion['correctAnswer'] === "D",
                "wrongDueToTimeOut" => false
            ];
        }
        $this->renderer->render("wrongAnswer", array_merge($completePayedQuestion, $flags, [
            "user_response" => $submittedAnswer,
            "questionId" => $completePayedQuestion['questionId'],
            "reported" => isset($_SESSION["reportedQuestionId"]),
            "responseTime" => $elapsedTime
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

    private function hasTimeRunOut() {
        $start = $_SESSION["currentGame"]["activeQuestion"]["timestamp"];
        $now = time();
        $elapsedTime = $now - $start;

        $timeout = $elapsedTime >= 15;
        return ["timeout" => $timeout, "elapsedTime" => $elapsedTime];
    }

    private function updateQuestionRatio($questionId, $wasCorrect) {
        $this->model->saveResponseRelatedData($questionId, $wasCorrect);
    }

    private function updateUserResponse($submittedAnswer, $questionId, $wasCorrect) {
        $this->model->updateUserResponseData($submittedAnswer, $questionId, $wasCorrect);
    }

    private function storeResults() {
        $this->model->storeGameResults($_SESSION["currentGame"]["gameId"]);
    }

    private function endGame($submittedAnswer) {
        $activeQuestionId = $_SESSION['currentGame']['activeQuestion']['id'];
        $this->updateQuestionRatio($activeQuestionId, false);
        $this->updateUserResponse($submittedAnswer, $activeQuestionId, false);
        $this->storeResults();
    }

    public function getAndDisplayNextQuestion($submittedAnswer) {
        $index = $_SESSION['currentGame']['currentQuestionIndex'];
        $playableQuestions = $_SESSION['currentGame']['playableQuestions'];
        $currentGameId = $_SESSION["currentGame"]["gameId"];
        $userId = $_SESSION["userId"];
        if (!isset($playableQuestions[$index])) {
            $this->getPlayableQuestionsAndSetGameData($currentGameId, $userId);
        }

        $activeQuestion = $playableQuestions[$index]->getIndividualPlayableQuestion(false);
        $_SESSION['currentGame']["activeQuestion"]['id'] = $activeQuestion['questionId'];
        $_SESSION['currentGame']['activeQuestion']['timestamp'] = time();

        $this->model->registerQuestionAssignment($userId, $activeQuestion['questionId'], $currentGameId);
        $activeQuestion['questionNumber'] = $index + 1;

        $this->renderer->render("displayGame", $activeQuestion);
    }

    public function resetUserProgressIfNoQuestions() {
        $this->model->resetUserQuestionHistory();
        $this->renderer->render("noMoreQuestions");
        exit;
    }
}