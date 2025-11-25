<?php

class RankingModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getPlayers($page = 1, $perPage = 4, $search = '', $countryId = null, $timeFilter = 'historico') {
        $offset = ($page - 1) * $perPage;

        $where = "WHERE u.id_rol = 3";

        if (!empty($search)) {
            $searchEscaped = $this->connection->real_escape_string($search);
            $where .= " AND u.nombre_usuario LIKE '%{$searchEscaped}%'";
        }

        $joins = "JOIN CIUDAD c ON u.id_ciudad = c.id 
                  JOIN PAIS pa ON c.id_pais = pa.id";

        if ($countryId) {
            $where .= " AND pa.id = " . (int)$countryId;
        }
        if ($timeFilter === 'historico' || empty($timeFilter)) {
            $scoreField = "u.puntos_totales";
            $timeCondition = "";
        } else {
            $dateCondition = $this->getDateCondition($timeFilter);
            $scoreField = "COALESCE((SELECT SUM(puntaje_jugador1) 
                                     FROM PARTIDA p 
                                     WHERE p.id_jugador1 = u.id {$dateCondition}), 0)";
        }

        $sql = "SELECT ranked.* FROM (
                    SELECT 
                        u.nombre_usuario AS username, 
                        {$scoreField} AS total_score,
                        u.foto AS avatar,
                        @rank := @rank + 1 AS rank
                    FROM USUARIO u
                    {$joins}
                    JOIN (SELECT @rank := 0) r
                    {$where}
                    ORDER BY total_score DESC, u.nombre_usuario ASC
                ) ranked
                LIMIT $perPage OFFSET $offset";

        return $this->connection->query($sql);
    }

    private function getDateCondition($timeFilter) {
        switch ($timeFilter) {
            case 'dia': return "AND DATE(p.fecha) = CURDATE()";
            case 'semana': return "AND p.fecha >= DATE_SUB(NOW(), INTERVAL 1 WEEK)"; // Últimos 7 días
            case 'mes': return "AND MONTH(p.fecha) = MONTH(CURRENT_DATE()) AND YEAR(p.fecha) = YEAR(CURRENT_DATE())";
            default: return "";
        }
    }

    public function countPlayers($search = '', $countryId = null) {

        $joins = "JOIN CIUDAD c ON u.id_ciudad = c.id JOIN PAIS pa ON c.id_pais = pa.id";
        $where = "WHERE u.id_rol = 3";

        if (!empty($search)) {
            $esc = $this->connection->real_escape_string($search);
            $where .= " AND u.nombre_usuario LIKE '%{$esc}%'";
        }
        if ($countryId) {
            $where .= " AND pa.id = " . (int)$countryId;
        }

        $sql = "SELECT COUNT(*) as total FROM USUARIO u {$joins} {$where}";
        $result = $this->connection->query($sql);
        return $result[0]['total'];
    }

    public function getUserRank($username, $countryId = null, $timeFilter = 'historico') {

        $myScore = $this->getUserScore($username, $timeFilter);

        if ($timeFilter === 'historico') {
            $scoreCol = "u.puntos_totales";
        } else {
            $dateCond = $this->getDateCondition($timeFilter);
            $scoreCol = "COALESCE((SELECT SUM(puntaje_jugador1) FROM PARTIDA p WHERE p.id_jugador1 = u.id {$dateCond}), 0)";
        }

        $joins = "JOIN CIUDAD c ON u.id_ciudad = c.id JOIN PAIS pa ON c.id_pais = pa.id";
        $countryWhere = $countryId ? "AND pa.id = " . (int)$countryId : "";

        if ($countryId) {
            $check = $this->connection->query("SELECT count(*) as ok FROM USUARIO u $joins WHERE u.nombre_usuario = '$username' $countryWhere");
            if ($check[0]['ok'] == 0) return '-';
        }

        $sql = "SELECT COUNT(*) + 1 as rank 
                FROM USUARIO u
                {$joins}
                WHERE u.id_rol = 3
                {$countryWhere}
                AND (
                    ({$scoreCol}) > $myScore
                    OR 
                    (({$scoreCol}) = $myScore AND u.nombre_usuario < '$username')
                )";

        $result = $this->connection->query($sql);
        return $result[0]['rank'] ?? null;
    }

    public function getUserScore($username, $timeFilter = 'historico') {
        if ($timeFilter === 'historico') {
            $sql = "SELECT puntos_totales FROM USUARIO WHERE nombre_usuario = '$username'";
            $result = $this->connection->query($sql);
            return $result[0]['puntos_totales'] ?? 0;
        } else {
            $dateCond = $this->getDateCondition($timeFilter);
            $sql = "SELECT SUM(p.puntaje_jugador1) as puntos
                    FROM PARTIDA p
                    JOIN USUARIO u ON p.id_jugador1 = u.id
                    WHERE u.nombre_usuario = '$username' {$dateCond}";
            $result = $this->connection->query($sql);
            return $result[0]['puntos'] ?? 0;
        }
    }

    public function getUserAvatar($username) {
        $sql = "SELECT foto FROM USUARIO WHERE nombre_usuario = '$username'";
        $result = $this->connection->query($sql);
        return isset($result[0]['foto']) ? $result[0]['foto'] : null;
    }

    public function getAllCountries() {
        $sql = "SELECT id, nombre FROM PAIS ORDER BY nombre ASC";
        return $this->connection->query($sql);
    }
}