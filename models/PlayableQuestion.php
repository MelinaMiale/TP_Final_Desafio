<?php
require_once __DIR__ . '/../enums/Categoria.php';
class PlayableQuestion {
    public $questionId;
    public $text;
    public $optionA;
    public $optionB;
    public $optionC;
    public $optionD;
    public $categoryName;
    public $categoryColor;
    public $categoryId;
    public $questionNumber;
    public $correctAnswer; // solo para uso interno, me permite usar la misma clase tanto para la logica del negocio como para mostrar en la vista.

    public function __construct($questionId, $text, $optionA, $optionB, $optionC, $optionD, $categoryName, $categoryColor, $categoryId, $questionNumber, $correctAnswer = null) {
        $this->questionId = $questionId;
        $this->text = $text;
        $this->optionA = $optionA;
        $this->optionB = $optionB;
        $this->optionC = $optionC;
        $this->optionD = $optionD;
        $this->categoryName = $categoryName;
        $this->categoryColor = $categoryColor;
        $this->categoryId = $categoryId;
        $this->questionNumber = $questionNumber;
        $this->correctAnswer = $correctAnswer;
    }

    public function getIndividualPlayableQuestion($includeCorrectAnswer = false) {
        $iconName = Categoria::ICON_MAP[$this->categoryId] ?? 'default';
        $data = [
            'questionId' => $this->questionId,
            'text' => $this->text,
            'optionA' => $this->optionA,
            'optionB' => $this->optionB,
            'optionC' => $this->optionC,
            'optionD' => $this->optionD,
            'categoryName' => $this->categoryName,
            'categoryColor' => $this->categoryColor,
            'questionNumber' => $this->questionNumber,
            'categoryIcon' => $iconName
        ];

        if ($includeCorrectAnswer) {
            $data['correctAnswer'] = $this->correctAnswer;
        }

        return $data;
    }
}