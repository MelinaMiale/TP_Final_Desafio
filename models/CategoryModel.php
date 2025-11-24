<?php

class CategoryModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function getCategoriesPaginated($limit, $offset, $searchCategory = null) {
        $sql = "SELECT id, nombre, color FROM CATEGORIA WHERE 1=1";
        if ($searchCategory) {
            $searchCategory = $this->connection->real_escape_string($searchCategory);
            $sql .= " AND nombre LIKE '%$searchCategory%'";
        }
        $sql .= " LIMIT $limit OFFSET $offset";

        return $this->connection->query($sql) ?? [];
    }

    public function getTotalCategoriesCount($searchCategory = null) {
        $sql = "SELECT COUNT(*) as total FROM CATEGORIA WHERE 1=1";
        if ($searchCategory) {
            $searchCategory = $this->connection->real_escape_string($searchCategory);
            $sql .= " AND nombre LIKE '%$searchCategory%'";
        }
        $result = $this->connection->query($sql);

        return $result ? $result[0]['total'] : 0;
    }

    public function getCategoryStats() {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'ACTIVO' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN estado = 'DESHABILITADO' THEN 1 ELSE 0 END) as disabled
            FROM CATEGORIA";
        $result = $this->connection->query($sql);
        return $result ? $result[0] : null;
    }

    public function getCategoryAuditHistory($limit = 20) {
        $sql = "SELECT * FROM AUDITORIA_CATEGORIA 
                ORDER BY fecha_cambio
                DESC LIMIT $limit";
        return $this->connection->query($sql);
    }

    public function addCategory($categoryData) {
        $activeStatus = 'ACTIVO';
        $color = $categoryData['color'];
        $categoryName = $categoryData['name'];

        $sql = "INSERT INTO CATEGORIA (nombre, color, estado) VALUES ('$categoryName', '$color', '$activeStatus')";
        $this->connection->query($sql);
    }

    public function updateCategory($categoryData) {
        $color = $categoryData['color'];
        $categoryName = $categoryData['name'];
        $categoryId = $categoryData['id'];

        $sql = "UPDATE CATEGORIA 
            SET nombre = '$categoryName',
            color = '$color'
            WHERE id = $categoryId";
        $this->connection->query($sql);
    }

    public function logEditorActivity($categoryData) {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $date = date("Y-m-d H:i:s");
        $newName = $categoryData['name'];
        $newColor = $categoryData['color'];
        $id = $categoryData['id'];
        $editorComment = $categoryData['editorComment'];
        $action = $categoryData['action'];
        $editorId = $_SESSION["userId"];
        $previousCategoryVersion = $this->getCategoryById($id);
        $previousName = $previousCategoryVersion['nombre'];
        $previousColor = $previousCategoryVersion['color'];

        $sql = "INSERT INTO AUDITORIA_CATEGORIA (id_categoria, nombre_antiguo, nombre_nuevo, color_antiguo, color_nuevo, comentario_editor, accion, id_editor, fecha_cambio) VALUES 
                ($id, '$previousName', '$newName', '$previousColor', '$newColor', '$editorComment', '$action', $editorId, '$date')";

        $this->connection->query($sql);
    }

    public function getCategoryById($categoryId) {
        $sql = "SELECT id, nombre, color 
            FROM CATEGORIA
            WHERE id = $categoryId";
        $result = $this->connection->query($sql);
        return $result ? $result[0] : null;
    }

    public function getAllCategories() {
        $sql = "SELECT id, nombre, color 
            FROM CATEGORIA";
        return $this->connection->query($sql);
    }
    public function disableCategory($categoryId, $editorComment) {
        $sql = "UPDATE CATEGORIA 
            SET estado = 'DESHABILITADO'
            WHERE id = $categoryId";
        $this->connection->query($sql);

        $editorId = $_SESSION["userId"];
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $date = date("Y-m-d H:i:s");
        $sqlAudit = "INSERT INTO AUDITORIA_CATEGORIA (id_categoria, comentario_editor, accion, id_editor, fecha_cambio) VALUES ($categoryId, '$editorComment', 'DESHABILITAR', $editorId, '$date')";
        $this->connection->query($sqlAudit);
    }

}