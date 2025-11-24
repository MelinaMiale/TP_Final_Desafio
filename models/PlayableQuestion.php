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
    public $correctAnswer;
    public $numCorrectAnswers;
    public $numTotalAnswers;
    public $ratio;
    public $difficultyLevel;

    public function __construct($questionId, $text, $optionA, $optionB, $optionC, $optionD, $categoryName, $categoryColor, $categoryId, $questionNumber, $correctAnswer = null, $numCorrectAnswers, $numTotalAnswers, $ratio, $difficultyLevel) {
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
        $this->numCorrectAnswers = $numCorrectAnswers;
        $this->numTotalAnswers = $numTotalAnswers;
        $this->ratio = $ratio;
        $this->difficultyLevel = $difficultyLevel;
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
            $data['numCorrectAnswers'] = $this->numCorrectAnswers;
            $data['numTotalAnswers'] = $this->numTotalAnswers;
            $data['ratio'] = $this->ratio;
            $data['difficultyLevel'] = $this->difficultyLevel;
        }

        return $data;
    }
}