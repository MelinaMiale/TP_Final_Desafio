<?php

class QuestionController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function addQuestion() {
        $questionData = [
            'statement' => $_POST['statement'],
            'optionA' => $_POST['optionA'],
            'optionB' => $_POST['optionB'],
            'optionC' => $_POST['optionC'],
            'optionD' => $_POST['optionD'],
            'correctAnswer' => $_POST['correctAnswer'],
            'categoryId' => $_POST['categoryId']
        ];
        $responseId = $this->model->addResponse($questionData);
        $questionData["responseId"] = $responseId;
        $this->model->addQuestion($questionData);
        header("Location: ?controller=question&method=manageQuestions");
        exit;
    }

    public function newQuestion() {
        $categories = $this->model->getAllCategories();
        foreach ($categories as &$cat) {
            $cat['isSelected'] = false;
        }

        $stats = $this->model->getQuestionStats();

        $categoryId = $_GET['categoryId'] ?? null;
        $statusId = $_GET['statusId'] ?? null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $questions = $this->model->getQuestionsPaginated($limit, $offset, $categoryId, $statusId);
        $totalQuestions = $this->model->getTotalQuestionsCount($categoryId, $statusId);
        $totalPages = max(1, (int)ceil($totalQuestions / $limit));

        $pages = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            $pages[] = [
                'number' => $i,
                'selected' => ($i === $page),
                'categoryId' => $categoryId,
                'statusId' => $statusId
            ];
        }

        $emptyQuestion = [
            'id' => null,
            'enunciado' => '',
            'opcion_a' => '',
            'opcion_b' => '',
            'opcion_c' => '',
            'opcion_d' => '',
            'respuesta_correcta' => ''
        ];

        $this->renderer->render("questions", [
            'questions' => $questions,
            'hasQuestions' => !empty($questions),
            'categories' => $categories,
            'statuses' => $this->model->getAllStatuses(),
            'totalQuestions' => $stats['total'],
            'approvedCount' => $stats['approved'],
            'pendingCount' => $stats['pending'],
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pages' => $pages,
            'questionToEdit' => $emptyQuestion
        ]);
    }

    public function editQuestion() {
        $reportId = $_SESSION["reportId"] ?? null;
        $questionId = $_POST["questionId"] ?? null;

        if ($reportId) {
            $question = $this->model->getQuestionByReportId($reportId);
        } elseif ($questionId) {
            $question = $this->model->getQuestionById($questionId);
        } else {
            header("Location: ?controller=question&method=manageQuestions");
            exit;
        }

        $categories = $this->model->getAllCategories();
        foreach ($categories as &$cat) {
            $cat['isSelected'] = ($cat['id'] == $question['id_categoria']);
        }

        $stats = $this->model->getQuestionStats();
        $questions = $this->model->getAllQuestions();

        $this->renderer->render("questions", [
            'questions' => $questions,
            'categories' => $categories,
            'totalQuestions' => $stats['total'],
            'approvedCount' => $stats['approved'],
            'pendingCount' => $stats['pending'],
            'questionToEdit' => $question,
            'reportId' => $reportId,
            'questionId' => $questionId
        ]);
    }

    public function updateQuestion() {
        $reportId = $_SESSION["reportId"] ?? null;
        $questionId = $_POST['questionId'] ?? null;
        $editorComment = $_POST['editorComment'] ?? '';
        $editorId = $_SESSION['userId'];

        $data = [
            'statement' => $_POST['statement'],
            'optionA' => $_POST['optionA'],
            'optionB' => $_POST['optionB'],
            'optionC' => $_POST['optionC'],
            'optionD' => $_POST['optionD'],
            'correctAnswer' => $_POST['correctAnswer'],
            'categoryId' => $_POST['categoryId']
        ];

        if ($reportId) {
            $questionData = $this->model->getQuestionByReportId($reportId);
            $questionId = $questionData['id'];
            $responseId = $questionData['respuesta_id'];

            $this->model->updateResponse($responseId, $data);
            $this->model->updateQuestion($questionId, $data);
            $this->model->finalizeReport($reportId, $editorComment, $editorId);
            unset($_SESSION["reportId"]);
        } elseif ($questionId) {
            $responseId = $this->model->getResponseByQuestionId($questionId);
            $this->model->updateResponse($responseId, $data);
            $this->model->updateQuestion($questionId, $data);
            $this->model->logEditorActivity($questionId, $editorComment, $editorId);
        }

        header("Location: ?controller=question&method=manageQuestions");
        exit;
    }


    public function disableQuestion() {
        $questionId = $_POST['id'] ?? null;
        $this->model->disableQuestion($questionId);
        header("Location: ?controller=question&method=manageQuestions");
        exit;
    }

    public function manageQuestions() {
        unset($_SESSION["reportId"]); // Con esto contemplo los casos de arrepentimiento del editor. :P

        $categoryId = $_GET['categoryId'] ?? null;
        $statusId = $_GET['statusId'] ?? null;
        $searchText = $_GET['searchText'] ?? null;

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $questions = $this->model->getQuestionsPaginated($limit, $offset, $categoryId, $statusId, $searchText);
        $totalQuestions = $this->model->getTotalQuestionsCount($categoryId, $statusId, $searchText);
        $totalPages = max(1, (int)ceil($totalQuestions / $limit));

        $pages = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            $pages[] = [
                'number' => $i,
                'selected' => ($i === $page),
                'categoryId' => $categoryId,
                'statusId' => $statusId,
                'searchText' => $searchText
            ];
        }

        $categories = $this->model->getAllCategories();
        $statuses = $this->model->getAllStatuses();
        $stats = $this->model->getQuestionStats();

        $this->renderer->render("questions", [
            'questions' => $questions ?? [],
            'hasQuestions' => !empty($questions),
            'categories' => $categories,
            'statuses' => $statuses,
            'totalQuestions' => $stats['total'],
            'approvedCount' => $stats['approved'],
            'pendingCount' => $stats['pending'],
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pages' => $pages,
            'searchText'     => $searchText
        ]);
    }

}