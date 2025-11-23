<?php

class SuggestedquestionsController {

    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function displayForm() {
        $this->renderer->render("suggestQuestion");
    }

}