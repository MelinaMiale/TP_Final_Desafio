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
        $sql = "SELECT a.id, 
               p.enunciado AS preguntaEnunciado,
               u.nombre_usuario AS usuarioNombre,
               a.comentario_usuario, 
               a.fecha_reporte,
               eo.nombre AS estado_origen
        FROM AUDITORIA_PREGUNTA a
        JOIN PREGUNTA p ON a.id_pregunta = p.id
        JOIN USUARIO u ON a.id_solicitante = u.id
        JOIN ESTADO_PREGUNTA eo ON a.estado_origen = eo.id
        WHERE a.estado_destino = $pendingQuestionStatus";
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

    public function resolveReport($reportId, $action, $editorComment, $editorId) {
        $date = date("Y-m-d H:i:s");

        // Determinar estado_destino según la acción
        switch ($action) {
            case 'aprobar':
                $statusDestination = QuestionStatus::APPROVED;
                break;
            case 'rechazar':
                $statusDestination = QuestionStatus::REJECTED;
                break;
            case 'dar_baja':
                $statusDestination = QuestionStatus::DISABLED;
                break;
            case 'modificar':
                $statusDestination = QuestionStatus::MODIFIED;
                break;
            default:
                $statusDestination = QuestionStatus::PENDING;
        }

        $sql = "UPDATE AUDITORIA_PREGUNTA
            SET id_editor = $editorId,
                accion = '$action',
                comentario_editor = '$editorComment',
                fecha_revision = '$date',
                estado_destino = $statusDestination
            WHERE id = $reportId";

        $this->connection->query($sql);
    }
}