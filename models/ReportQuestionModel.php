<?php
require_once __DIR__ . '/../enums/QuestionStatus.php';
class ReportQuestionModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function reportProposedStatement ($reason, $questionId) {
        $userId = $_SESSION["userId"];
        $questionStatus = QuestionStatus::PENDING;
        $date = date("Y-m-d H:i:s");
        $reason = 'Se reporta el enunciado: ' . $reason;
        $sql = "INSERT INTO AUDITORIA_PREGUNTA (id_solicitante, id_pregunta, id_estado_pregunta, fecha, motivo) VALUES ($userId, $questionId, $questionStatus, '$date', '$reason')";
        $this->connection->query($sql);
    }
    public function reportProposedAnswer ($proposedAnswer, $questionId) {
        $userId = $_SESSION["userId"];
        $questionStatus = QuestionStatus::PENDING;
        $date = date("Y-m-d H:i:s");
        $reason = 'Se reporta el la respuesta: ' . $proposedAnswer;
        $sql = "INSERT INTO AUDITORIA_PREGUNTA (id_solicitante, id_pregunta, id_estado_pregunta, fecha, motivo) VALUES ($userId, $questionId, $questionStatus, '$date', '$reason')";
        $this->connection->query($sql);
    }

}