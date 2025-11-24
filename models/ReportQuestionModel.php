<?php
require_once __DIR__ . '/../enums/QuestionStatus.php';
class ReportQuestionModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function reportProposedStatement($reason, $questionId) {
        $userId = $_SESSION["userId"];
        $questionStatus = QuestionStatus::PENDING;
        $date = date("Y-m-d H:i:s");
        $reason = 'Se reporta el enunciado: ' . $reason;

        // todo: estado_origen ahora es siempre "approved" , si la pregunta estaba en otro estado (ej. MODIFIED), entonces deberia guardar el estado actual.
        $sqlAudit = "INSERT INTO AUDITORIA_PREGUNTA 
        (id_solicitante, id_pregunta, comentario_usuario, estado_origen, estado_destino, fecha_reporte) 
        VALUES ($userId, $questionId, '$reason', " . QuestionStatus::APPROVED . ", $questionStatus, '$date')";
        $this->connection->query($sqlAudit);

        $sqlUpdateQuestionStatus = "UPDATE PREGUNTA 
                SET id_estado_pregunta = $questionStatus
                WHERE id = $questionId";
        $this->connection->query($sqlUpdateQuestionStatus);
    }

    public function reportProposedAnswer ($proposedAnswer, $questionId) {
        $userId = $_SESSION["userId"];
        $questionStatus = QuestionStatus::PENDING;
        $date = date("Y-m-d H:i:s");
        $reason = 'Se reporta la respuesta: ' . $proposedAnswer;


        // todo: estado_origen ahora es siempre "approved" , si la pregunta estaba en otro estado (ej. MODIFIED), entonces deberia guardar el estado actual.
        $sqlAudit = "INSERT INTO AUDITORIA_PREGUNTA 
        (id_solicitante, id_pregunta, comentario_usuario, estado_origen, estado_destino, fecha_reporte) 
        VALUES ($userId, $questionId, '$reason', " . QuestionStatus::APPROVED . ", $questionStatus, '$date')";
        $this->connection->query($sqlAudit);

        $sqlUpdateQuestionStatus = "UPDATE PREGUNTA 
                SET id_estado_pregunta = $questionStatus
                WHERE id = $questionId";
        $this->connection->query($sqlUpdateQuestionStatus);
    }

    public function hasAlreadyReportedQuestionInGame($userId, $gameId) {
        $sql = "SELECT EXISTS(
                SELECT 1
                FROM AUDITORIA_PREGUNTA a
                JOIN RESPUESTA_USUARIO r 
                  ON a.id_pregunta = r.id_pregunta 
                 AND a.id_solicitante = r.id_usuario
                WHERE r.id_partida = $gameId
                  AND r.id_usuario = $userId
            ) AS reported";

        $result = $this->connection->query($sql);
        if ($result && isset($result[0]['reported'])) {
            return (bool)$result[0]['reported'];
        }

        return false;
    }

}