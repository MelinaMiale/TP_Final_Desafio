<?php

class QuestionController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function addQuestion() {
        $data = [
            'statement' => $_POST['statement'],
            'optionA' => $_POST['optionA'],
            'optionB' => $_POST['optionB'],
            'optionC' => $_POST['optionC'],
            'optionD' => $_POST['optionD'],
            'correctAnswer' => $_POST['correctAnswer'],
            'categoryId' => $_POST['categoryId']
        ];
        $this->model->insertQuestion($data);
        header("Location: ?controller=question&method=manageQuestions");
        exit;
    }

    public function editQuestion() {
        $reportId = $_GET['reportId'] ?? null;
//        var_dump("reporte id: " . $reportId);
//        exit;
        if (!$reportId) {
            header("Location: ?controller=question&method=manageQuestions");
            exit;
        }

        $question = $this->model->getQuestionByReportId($reportId);
        $this->renderer->render("questions", $question);
    }

    public function updateQuestion() {
        $id = $_POST['id'];
        $data = [
            'statement' => $_POST['statement'],
            'optionA' => $_POST['optionA'],
            'optionB' => $_POST['optionB'],
            'optionC' => $_POST['optionC'],
            'optionD' => $_POST['optionD'],
            'correctAnswer' => $_POST['correctAnswer'],
            'categoryId' => $_POST['categoryId']
        ];
        $this->model->updateQuestion($id, $data);
        header("Location: ?controller=question&method=manageQuestions");
        exit;
    }

    public function deleteQuestion() {
        $id = $_POST['id'];
        $this->model->deleteQuestion($id);
        header("Location: ?controller=question&method=manageQuestions");
        exit;
    }

    public function manageQuestions() {
        $questions = $this->model->getAllQuestions();
        $categories = $this->model->getAllCategories();
        $stats = $this->model->getQuestionStats();
        $this->renderer->render("questionsCRUD", [
            'questions' => $questions,
            'categories' => $categories,
            'totalQuestions' => $stats['total'],
            'approvedCount' => $stats['approved'],
            'pendingCount' => $stats['pending']
        ]);
    }

}