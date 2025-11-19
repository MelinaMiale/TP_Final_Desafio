<?php

class QuestionModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function insertQuestion($data) {
        $sql = "INSERT INTO PREGUNTA (enunciado, opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta, id_categoria, estado)
                VALUES ('{$data['statement']}', '{$data['optionA']}', '{$data['optionB']}', '{$data['optionC']}', '{$data['optionD']}', '{$data['correctAnswer']}', {$data['categoryId']}, " . QuestionStatus::APPROVED . ")";
        $this->connection->query($sql);
    }

    public function getQuestionById($id) {
        $sql = "SELECT * FROM PREGUNTA WHERE id = $id";
        return $this->connection->query($sql)[0] ?? null;
    }

    public function getQuestionByReportId($reportId) {
        $sql = "SELECT p.* 
            FROM PREGUNTA p
            JOIN AUDITORIA_PREGUNTA a ON p.id = a.id_pregunta
            WHERE a.id = $reportId";
        return $this->connection->query($sql)[0] ?? null;
    }

    public function updateQuestion($id, $data) {
        $sql = "UPDATE PREGUNTA SET 
                    enunciado = '{$data['statement']}',
                    opcion_a = '{$data['optionA']}',
                    opcion_b = '{$data['optionB']}',
                    opcion_c = '{$data['optionC']}',
                    opcion_d = '{$data['optionD']}',
                    respuesta_correcta = '{$data['correctAnswer']}',
                    id_categoria = {$data['categoryId']}
                WHERE id = $id";
        $this->connection->query($sql);
    }

    public function deleteQuestion($id) {
        $sql = "DELETE FROM PREGUNTA WHERE id = $id";
        $this->connection->query($sql);
    }

    public function getAllQuestions() {
        $sql = "SELECT p.id, p.enunciado, p.respuesta_correcta, c.nombre AS categoria, e.nombre AS estado
                FROM PREGUNTA p
                JOIN CATEGORIA c ON p.id_categoria = c.id
                JOIN ESTADO_PREGUNTA e ON p.estado = e.id";
        return $this->connection->query($sql);
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM CATEGORIA";
        return $this->connection->query($sql);
    }

    public function getQuestionStats() {
        $sqlTotal = "SELECT COUNT(*) AS total FROM PREGUNTA";
        $sqlApproved = "SELECT COUNT(*) AS approved FROM PREGUNTA WHERE estado = " . QuestionStatus::APPROVED;
        $sqlPending = "SELECT COUNT(*) AS pending FROM PREGUNTA WHERE estado = " . QuestionStatus::PENDING;

        $total = $this->connection->query($sqlTotal)[0]['total'] ?? 0;
        $approved = $this->connection->query($sqlApproved)[0]['approved'] ?? 0;
        $pending = $this->connection->query($sqlPending)[0]['pending'] ?? 0;

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending
        ];
    }

}