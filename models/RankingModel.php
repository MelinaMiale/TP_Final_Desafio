<?php

class RankingModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getPlayers($page = 1, $perPage = 4, $search = '') {
        $offset = ($page - 1) * $perPage;
        $searchCondition = '';

        if (!empty($search)) {
            $searchEscaped = $this->connection->real_escape_string($search);
            $searchCondition = "AND username LIKE '%{$searchEscaped}%'";
        }

        $sql = "SELECT ranked.* FROM (
                SELECT u.nombre_usuario AS username, 
                       u.puntos_totales AS total_score,
                       u.foto AS avatar,
                       @rank := @rank + 1 AS rank
                FROM USUARIO u, (SELECT @rank := 0) r
                WHERE u.id_rol = 3
                ORDER BY u.puntos_totales DESC, u.nombre_usuario ASC
            ) ranked
            WHERE 1=1 {$searchCondition}
            LIMIT $perPage OFFSET $offset";

        return $this->connection->query($sql);
    }

    public function countPlayers($search = '') {
        $searchCondition = '';
        if (!empty($search)) {
            $searchEscaped = $this->connection->real_escape_string($search);
            $searchCondition = "AND nombre_usuario LIKE '%{$searchEscaped}%'";
        }
        $sql = "SELECT COUNT(*) as total 
            FROM USUARIO 
            WHERE id_rol = 3
            {$searchCondition}";

        $result = $this->connection->query($sql);
        return $result[0]['total'];
    }

    public function getUserRank($username) {
        $sql = "SELECT COUNT(*) + 1 as rank 
                FROM USUARIO 
                WHERE id_rol = 3
                AND (
                    puntos_totales > (
                        SELECT puntos_totales 
                        FROM USUARIO 
                        WHERE nombre_usuario = '$username' AND id_rol = 3
                    )
                    OR (
                        puntos_totales = (
                            SELECT puntos_totales 
                            FROM USUARIO 
                            WHERE nombre_usuario = '$username' AND id_rol = 3
                        )
                        AND nombre_usuario < '$username'
                    )
                )";
        $result = $this->connection->query($sql);
        return $result[0]['rank'] ?? null;
    }

    public function getUserAvatar($username) {
        $sql = "SELECT foto FROM USUARIO WHERE nombre_usuario = '$username'";
        $result = $this->connection->query($sql);

        if (isset($result[0]['foto'])) {
            return $result[0]['foto'];
        } else {
            return null;
        }
    }

    public function getAllCountries() {
        $sql = "SELECT id, nombre FROM PAIS ORDER BY nombre ASC";
        return $this->connection->query($sql);
    }
    public function getUserScore($username) {
        $sql = "SELECT puntos_totales FROM USUARIO WHERE nombre_usuario = '$username'";
        $result = $this->connection->query($sql);

        if (isset($result[0]['puntos_totales'])) {
            return $result[0]['puntos_totales'];
        } else {
            return 0;
        }
    }
}