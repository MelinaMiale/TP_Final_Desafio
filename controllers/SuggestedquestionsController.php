<?php

class SuggestedquestionsController {

    private $model;
    private $renderer;

    private $categoryModel;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
        $this->categoryModel = new CategoryModel($this->model->getConnection());
    }

    public function displayForm() {
        $categories = $this->categoryModel->getAllCategories();
        $this->renderer->render("suggestQuestion", [
            'categories' => $categories
        ]);
    }

    public function suggestQuestion() {
        $suggestedQuestion = [
            'question' => $_POST['question'],
            'option_a' => $_POST['option_a'],
            'option_b' => $_POST['option_b'],
            'option_c' => $_POST['option_c'],
            'option_d' => $_POST['option_d'],
            'categoryId' => $_POST['categoryId'],
            'correct_answer' => $_POST['correct_answer']
        ];
        $this->model->saveSuggestData($suggestedQuestion);
        $this->renderer->render("suggestionSubmitted");
    }
}