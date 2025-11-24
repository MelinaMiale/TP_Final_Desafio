<?php
require_once __DIR__ . '/../enums/QuestionStatus.php';
class ReportQuestionModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function reportProposedStatement($reason, $questionId) {
        $userId = $_SESSION["userId"];
        $questionStatus = QuestionStatus::PENDING; // el estado_origen de la tabla auditoria_pregunta siempre debe coincidir con el id_estado_pregunta en la tabla pregunta, deberiamos cambiarle el nombre al campo para que sea: estado_actual
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $date = date("Y-m-d H:i:s");
        $reason = 'Se reporta el enunciado: ' . $reason;

        $sqlAudit = "INSERT INTO AUDITORIA_PREGUNTA 
        (id_solicitante, id_pregunta, comentario_usuario, estado_origen, fecha_reporte) 
        VALUES ($userId, $questionId, '$reason', $questionStatus, '$date')";
        $this->connection->query($sqlAudit);

        $sqlUpdateQuestionStatus = "UPDATE PREGUNTA 
                SET id_estado_pregunta = $questionStatus
                WHERE id = $questionId";
        $this->connection->query($sqlUpdateQuestionStatus);
    }

    public function reportProposedAnswer ($proposedAnswer, $questionId) {
        $userId = $_SESSION["userId"];
        $questionStatus = QuestionStatus::PENDING; // el estado_origen de la tabla auditoria_pregunta siempre debe coincidir con el id_estado_pregunta en la tabla pregunta, deberiamos cambiarle el nombre al campo para que sea: estado_actual
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $date = date("Y-m-d H:i:s");
        $reason = 'Se reporta la respuesta: ' . $proposedAnswer;

        $sqlAudit = "INSERT INTO AUDITORIA_PREGUNTA 
        (id_solicitante, id_pregunta, comentario_usuario, estado_origen, fecha_reporte) 
        VALUES ($userId, $questionId, '$reason', $questionStatus, '$date')";
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