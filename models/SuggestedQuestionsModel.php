<?php
require_once __DIR__ . '/../enums/QuestionStatus.php';

class SuggestedQuestionsModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function saveSuggestData($suggestedData) {
        $newResponseId = $this->addResponse($suggestedData);
        $newQuestionId = $this->addQuestion($suggestedData, $newResponseId);

        $this->addAuditInformation($newQuestionId);
    }

    public function addQuestion($data, $newResponseId) {
        $pendingStatus = QuestionStatus::PENDING;
        $idCreator = $_SESSION["userId"];
        $date = date("Y-m-d H:i:s");

        $sql = "INSERT INTO PREGUNTA (enunciado, id_respuesta, id_categoria, id_estado_pregunta, id_autor, fecha_creacion) VALUES ('{$data['question']}', $newResponseId, {$data['categoryId']}, $pendingStatus,  $idCreator, '$date')";

        $this->connection->query($sql);
        return $this->connection->getLastInsertId();
    }

    public function addResponse($data) {
        $optionA = $data['option_a'];
        $optionB = $data['option_b'];
        $optionC = $data['option_c'];
        $optionD = $data['option_d'];
        $correctAnswer = $data['correct_answer'];

        $sql = "INSERT INTO RESPUESTA (opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta)
            VALUES ('$optionA', '$optionB', '$optionC', '$optionD', '$correctAnswer')";

        $this->connection->query($sql);
        return $this->connection->getLastInsertId();
    }

    public function addAuditInformation($newQuestionId) {
        $pendingStatus = QuestionStatus::PENDING;
        $comment = 'Pregunta sugerida por jugador';
        $date = date("Y-m-d H:i:s");
        $userId = $_SESSION["userId"];
        $sqlAudit = "INSERT INTO AUDITORIA_PREGUNTA 
        (id_solicitante, id_pregunta, comentario_usuario, estado_origen, estado_destino, fecha_reporte) 
        VALUES ($userId, $newQuestionId, '$comment', $pendingStatus, $pendingStatus, '$date')";
        $this->connection->query($sqlAudit);
    }
}