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

        if(!empty($_POST["proposedStatement"])){
            $proposedStatement = $_POST["proposedStatement"];
            $this->model->reportProposedStatement($proposedStatement, $questionId);
        } else if(!empty($_POST["selectedOption"]) || !empty($_POST["proposedAnswer"])){
            $selectedOption = $_POST["selectedOption"];
            $userAnswer = $_POST["proposedAnswer"];
            $proposedAnswer = $userAnswer || $selectedOption;
            $this->model->reportProposedAnswer($proposedAnswer, $questionId);
        }

        header("Location: ?controller=game&method=resumeAfterReport");
        exit;
    }

}