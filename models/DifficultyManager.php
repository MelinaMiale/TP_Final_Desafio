<?php
require_once __DIR__ . '/../enums/QuestionDifficulty.php';

class DifficultyManager {
    private $connection;
    public function __construct($connection) {
        $this->connection = $connection;
    }

    private function determineLevel($ratio) {
        if ($ratio >= 0.7) {
            return QuestionDifficulty::HARD;
        } elseif ($ratio >= 0.3) {
            return QuestionDifficulty::MEDIUM;
        } else {
            return QuestionDifficulty::EASY;
        }
    }

    private function calculateRatio($numCorrectAnswers, $numTotalAnswers) {
        return $numCorrectAnswers/$numTotalAnswers;
    }

    public function updateQuestionDifficulty($question, $wasCorrect) {
        if ($question === null) {
            return;
        }
        $numCorrectAnswers = $wasCorrect ? $question->numCorrectAnswers + 1 : $question->numCorrectAnswers;
        $numTotalAnswers = $question->numTotalAnswers + 1;
        $ratio = $this->calculateRatio($numCorrectAnswers, $numTotalAnswers);
        $difficultyLevel = $this->determineLevel($ratio);
        $questionId = $question->questionId;

        $sql = "UPDATE PREGUNTA
        SET respuestas_correctas = $numCorrectAnswers,
            respuestas_totales = $numTotalAnswers,
            dificultad_actual = $difficultyLevel,
            ratio_aciertos = $ratio
            WHERE id = $questionId";

        $this->connection->query($sql);
    }

}