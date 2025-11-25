<?php

class ReportquestionController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function reportQuestion() {
        $questionId = $_POST["questionId"];
        $playableQuestions = $_SESSION["currentGame"]["playableQuestions"];

        $payedQuestion = null;
        foreach ($playableQuestions as $pq) {
            if ($pq->questionId == $questionId) {
                $payedQuestion = $pq;
                break;
            }
        }

        $completeQuestion = $payedQuestion->getIndividualPlayableQuestion(true);
        if ($this->model->hasAlreadyReportedQuestionInGame($_SESSION["userId"], $_SESSION["currentGame"]["gameId"])) {
            $_SESSION['reported'] = true;
            echo "<p>Ya has reportado una pregunta en la partida, has perdido.</p>";
            header("Location: ?controller=game&method=resumeAfterReport");
            exit;
        }
        $this->renderer->render("reportQuestion", $completeQuestion);
    }
    public function submitReport() {
        $questionId = $_POST["questionId"];
        if (!empty($_POST["proposedStatement"])) {
            $reason = trim($_POST["proposedStatement"]);
            $this->model->reportProposedStatement($reason, $questionId);
        }


        if (!empty($_POST["selectedOption"]) || !empty($_POST["proposedAnswer"])) {

            if (!empty($_POST["selectedOption"])) {
                $proposedAnswer = trim($_POST["selectedOption"]);
            }
            else {
                $proposedAnswer = trim($_POST["proposedAnswer"]);
            }

            $this->model->reportProposedAnswer($proposedAnswer, $questionId);
        }

        $_SESSION['reported'] = true;

        header("Location: ?controller=game&method=resumeAfterReport");
        exit;
    }


}