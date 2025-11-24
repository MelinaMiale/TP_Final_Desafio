<?php

class QuestionModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function addQuestion($data) {
        $approvedStatus = QuestionStatus::APPROVED;
        $responseId = $data["responseId"];
        $idCreator = $_SESSION["userId"];
        $date = date("Y-m-d H:i:s");

        $sql = "INSERT INTO PREGUNTA (enunciado, id_respuesta, id_categoria, id_estado_pregunta, id_autor, fecha_creacion) VALUES ('{$data['statement']}', $responseId, {$data['categoryId']}, $approvedStatus,  $idCreator, '$date')";

        $this->connection->query($sql);
    }

    public function addResponse($data) {
        $optionA = $data['optionA'];
        $optionB = $data['optionB'];
        $optionC = $data['optionC'];
        $optionD = $data['optionD'];
        $correctAnswer = $data['correctAnswer'];

        $sql = "INSERT INTO RESPUESTA (opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta)
            VALUES ('$optionA', '$optionB', '$optionC', '$optionD', '$correctAnswer')";

        $this->connection->query($sql);
        return $this->connection->getLastInsertId();
    }


    public function getQuestionById($questionId) {
        $sql = "SELECT 
                p.id,
                p.enunciado,
                r.id AS respuesta_id,
                r.opcion_a,
                r.opcion_b,
                r.opcion_c,
                r.opcion_d,
                r.respuesta_correcta,
                p.id_categoria,
                c.nombre AS categoria_nombre
            FROM PREGUNTA p
            JOIN RESPUESTA r ON p.id_respuesta = r.id
            JOIN CATEGORIA c ON p.id_categoria = c.id
            WHERE p.id = $questionId";
        $result = $this->connection->query($sql);
        return $result ? $result[0] : null;
    }

    public function getQuestionByReportId($reportId) {
        $sql = "SELECT 
                p.id,
                p.enunciado,
                r.id AS respuesta_id,
                r.opcion_a,
                r.opcion_b,
                r.opcion_c,
                r.opcion_d,
                r.respuesta_correcta,
                p.id_categoria,
                c.nombre AS categoria_nombre,
                a.id AS reportId,
                a.comentario_usuario,
                a.comentario_editor,
                a.fecha_reporte,
                a.accion,
                a.fecha_revision
            FROM PREGUNTA p
            JOIN RESPUESTA r ON p.id_respuesta = r.id
            JOIN AUDITORIA_PREGUNTA a ON p.id = a.id_pregunta
            JOIN CATEGORIA c ON p.id_categoria = c.id
            WHERE a.id = $reportId";

        return $this->connection->query($sql)[0] ?? null;
    }

    public function updateResponse($responseId, $responseData) {
            $sql = "UPDATE RESPUESTA SET 
                opcion_a = '{$responseData['optionA']}',
                opcion_b = '{$responseData['optionB']}',
                opcion_c = '{$responseData['optionC']}',
                opcion_d = '{$responseData['optionD']}',
                respuesta_correcta = '{$responseData['correctAnswer']}'
            WHERE id = $responseId";
            $this->connection->query($sql);
    }

    public function updateQuestion($questionId, $questionData) {
        $approved = QuestionStatus::APPROVED;
        $sql = "UPDATE PREGUNTA SET 
                    enunciado = '{$questionData['statement']}',
                    id_categoria = {$questionData['categoryId']},
                    id_estado_pregunta = $approved
                WHERE id = $questionId";
        $this->connection->query($sql);
    }

    public function finalizeReport($reportId, $editorComment, $editorId) {
        $date = date("Y-m-d H:i:s");
        $approved = QuestionStatus::APPROVED;

        $sql = "UPDATE AUDITORIA_PREGUNTA SET 
                accion = 'modificado',
                comentario_editor = '$editorComment',
                id_editor = $editorId,
                fecha_revision = '$date',
                estado_destino = $approved
            WHERE id = $reportId";
        $this->connection->query($sql);
    }

    public function getResponseByQuestionId($questionId) {
        $sql = "SELECT id_respuesta 
            FROM PREGUNTA 
            WHERE id = $questionId";
        $result = $this->connection->query($sql);
        return $result ? $result[0]['id_respuesta'] : null;
    }

    public function logEditorActivity($questionId, $editorComment, $editorId) {
        $date = date("Y-m-d H:i:s");
        $approved = QuestionStatus::APPROVED;

        $sql = "INSERT INTO AUDITORIA_PREGUNTA 
                (id_pregunta, accion, comentario_editor, id_editor, fecha_revision, estado_destino, estado_origen) 
            VALUES 
                ($questionId, 'modificado', '$editorComment', $editorId, '$date', $approved, $approved)";
        $this->connection->query($sql);
    }

    public function disableQuestion($id) {
        $disabledStatus = QuestionStatus::DISABLED;
        $sql = "UPDATE PREGUNTA 
            SET id_estado_pregunta = $disabledStatus
            WHERE id = $id";
        $this->connection->query($sql);
    }

    public function getQuestionsPaginated($limit = 10, $offset = 0, $categoryId = null, $statusId = null, $searchText = null) {
        $sql = "SELECT p.id, 
                   p.enunciado, 
                   r.respuesta_correcta,
                   c.nombre AS categoria, 
                   e.nombre AS estado
            FROM PREGUNTA p
            JOIN RESPUESTA r ON p.id_respuesta = r.id
            JOIN CATEGORIA c ON p.id_categoria = c.id
            JOIN ESTADO_PREGUNTA e ON p.id_estado_pregunta = e.id
            WHERE 1=1";

        if ($categoryId) {
            $sql .= " AND p.id_categoria = " . (int)$categoryId;
        }
        if ($statusId) {
            $sql .= " AND p.id_estado_pregunta = " . (int)$statusId;
        }
        if ($searchText) {
            $searchText = $this->connection->real_escape_string($searchText);
            $sql .= " AND p.enunciado LIKE '%$searchText%'";
        }

        $sql .= " LIMIT $limit OFFSET $offset";

        return $this->connection->query($sql) ?? [];
    }

    public function getTotalQuestionsCount($categoryId = null, $statusId = null, $searchText = null) {
        $sql = "SELECT COUNT(*) AS total FROM PREGUNTA WHERE 1=1";
        if ($categoryId) {
            $sql .= " AND id_categoria = " . (int)$categoryId;
        }
        if ($statusId) {
            $sql .= " AND id_estado_pregunta = " . (int)$statusId;
        }
        if ($searchText) {
            $searchText = $this->connection->real_escape_string($searchText);
            $sql .= " AND enunciado LIKE '%$searchText%'";
        }

        $result = $this->connection->query($sql);
        return $result ? $result[0]['total'] : 0;
    }

    public function getAllQuestions() {
        $sql = "SELECT p.id, 
                p.enunciado, 
                r.respuesta_correcta,
                r.opcion_a, 
                r.opcion_b, 
                r.opcion_c, 
                r.opcion_d,
                c.nombre AS categoria, 
                e.nombre AS estado
                FROM PREGUNTA p
                JOIN RESPUESTA r ON p.id_respuesta = r.id
                JOIN CATEGORIA c ON p.id_categoria = c.id
                JOIN ESTADO_PREGUNTA e ON p.id_estado_pregunta = e.id";
        return $this->connection->query($sql);
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM CATEGORIA";
        return $this->connection->query($sql);
    }

    public function getAllStatuses() {
        $sql = "SELECT * FROM ESTADO_PREGUNTA";
        return $this->connection->query($sql);
    }

    public function getQuestionStats() {
        $sqlTotal = "SELECT COUNT(*) AS total FROM PREGUNTA";
        $sqlApproved = "SELECT COUNT(*) AS approved FROM PREGUNTA WHERE id_estado_pregunta = " . QuestionStatus::APPROVED;
        $sqlPending = "SELECT COUNT(*) AS pending FROM PREGUNTA WHERE id_estado_pregunta = " . QuestionStatus::PENDING;

        $total = $this->connection->query($sqlTotal)[0]['total'] ?? 0;
        $approved = $this->connection->query($sqlApproved)[0]['approved'] ?? 0;
        $pending = $this->connection->query($sqlPending)[0]['pending'] ?? 0;

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending
        ];
    }

    public function reassignQuestions($oldCategoryId, $newCategoryId, $editorComment) {
        $sql = "UPDATE PREGUNTA SET id_categoria = $newCategoryId WHERE id_categoria = $oldCategoryId";
        $this->connection->query($sql);

        //todo: ver esto.
//        $date = date("Y-m-d H:i:s");
//        $editorId = $_SESSION["userId"];
//
//        $sqlAudit = "INSERT INTO AUDITORIA_PREGUNTA (id_categoria_antigua, id_categoria_nueva, comentario_editor, id_editor, fecha_cambio)
//                 VALUES ($oldCategoryId, $newCategoryId, '$editorComment', $editorId, '$date')";
//        $this->connection->query($sqlAudit);
    }

    public function getQuestionsByCategoryId($categoryId) {
        $categoryId = (int) $categoryId;
        $sql = "SELECT id, enunciado 
                FROM PREGUNTA 
                WHERE id_categoria = $categoryId";

        return $this->connection->query($sql);
    }


}