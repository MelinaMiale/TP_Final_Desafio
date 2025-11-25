<?php

class EditorModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getEditorMetrics() {
        $pendingReportsQuery = "SELECT COUNT(*) AS total 
            FROM AUDITORIA_PREGUNTA 
            WHERE estado_destino IS NULL";
        $pendingReports = $this->connection->query($pendingReportsQuery);

        $resolvedReportsQuery = "SELECT COUNT(*) AS total 
            FROM AUDITORIA_PREGUNTA 
            WHERE estado_destino IS NOT NULL";
        $resolvedReports = $this->connection->query($resolvedReportsQuery);

        $avgResolutionTimeQuery = "SELECT AVG(DATEDIFF(fecha_revision, fecha_reporte)) AS promedio
            FROM AUDITORIA_PREGUNTA
            WHERE fecha_revision IS NOT NULL";
        $avgResolutionTime = $this->connection->query($avgResolutionTimeQuery);

        return [
            "pendingReports" => $pendingReports ? $pendingReports[0]['total'] : 0,
            "resolvedReports" => $resolvedReports ? $resolvedReports[0]['total'] : 0,
            "avgResolutionTime" => $avgResolutionTime && $avgResolutionTime[0]['promedio'] !== null
                ? round($avgResolutionTime[0]['promedio'], 1)
                : 0
        ];
    }

    public function getPendingReports() {
        $pendingQuestionStatus = QuestionStatus::PENDING;
        $sql = "SELECT a.id AS reportId, 
               p.enunciado AS preguntaEnunciado,
               u.nombre_usuario AS usuarioNombre,
               a.comentario_usuario, 
               a.fecha_reporte,
               eo.nombre AS estado_origen
        FROM AUDITORIA_PREGUNTA a
        JOIN PREGUNTA p ON a.id_pregunta = p.id
        JOIN USUARIO u ON a.id_solicitante = u.id
        JOIN ESTADO_PREGUNTA eo ON a.estado_origen = eo.id
        WHERE 
      p.id_estado_pregunta = $pendingQuestionStatus
   OR a.estado_origen = $pendingQuestionStatus
   OR a.estado_destino = $pendingQuestionStatus";
        return $this->connection->query($sql) ?? [];
    }


    public function getHistory() {
        $sql = "
            SELECT p.enunciado AS preguntaEnunciado,
                   u.nombre_usuario AS usuarioNombre,
                   a.comentario_usuario, a.accion,
                   a.comentario_editor,
                   eo.nombre AS estado_origen,
                   ed.nombre AS estado_destino,
                   a.fecha_reporte, a.fecha_revision
            FROM AUDITORIA_PREGUNTA a
            JOIN PREGUNTA p ON a.id_pregunta = p.id
            JOIN USUARIO u ON a.id_solicitante = u.id
            JOIN ESTADO_PREGUNTA eo ON a.estado_origen = eo.id
            LEFT JOIN ESTADO_PREGUNTA ed ON a.estado_destino = ed.id
            WHERE a.estado_destino IS NOT NULL
            ORDER BY a.fecha_revision DESC
            LIMIT 20
        ";
        return $this->connection->query($sql) ?? [];
    }

    public function rejectReport($reportId, $editorComment, $editorId) {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $date = date("Y-m-d H:i:s");
        $statusDestination = QuestionStatus::REJECTED;

        $sqlAudit = "UPDATE AUDITORIA_PREGUNTA
            SET id_editor = $editorId,
                accion = 'rechazar',
                comentario_editor = '$editorComment',
                fecha_revision = '$date',
                estado_destino = $statusDestination,
                estado_origen = $statusDestination
            WHERE id = $reportId";
        $this->connection->query($sqlAudit);

        $sqlGetQuestion = "SELECT id_pregunta FROM AUDITORIA_PREGUNTA WHERE id = $reportId";
        $result = $this->connection->query($sqlGetQuestion);
        if ($result && isset($result[0]['id_pregunta'])) {
            $questionId = $result[0]['id_pregunta'];

            $sqlUpdateQuestionStatus = "UPDATE PREGUNTA 
            SET id_estado_pregunta = " . QuestionStatus::APPROVED . "
            WHERE id = $questionId";
            $this->connection->query($sqlUpdateQuestionStatus);
        }
    }

}