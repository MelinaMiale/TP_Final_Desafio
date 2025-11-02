<?php
require_once __DIR__ . '/../enums/Role.php';

class RankingModel {
    private $connection;
    private $playerRole;

    public function __construct($connection) {
        $this->connection = $connection;
        $this->playerRole = Role::PLAYER;
    }

    public function getPlayers($page = 1, $perPage = 4) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT u.nombre_usuario AS username, 
                       u.puntos_totales AS total_score,
                       u.foto AS avatar
                FROM usuario u 
                WHERE u.id_rol = {$this->playerRole}
                ORDER BY u.puntos_totales DESC, u.nombre_usuario ASC
                LIMIT $perPage OFFSET $offset";
        return $this->connection->query($sql);
    }

    public function countPlayers() {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE id_rol = {$this->playerRole}";
        $result = $this->connection->query($sql);
        return $result[0]['total'];
    }

    public function getUserRank($username) {
        $sql = "SELECT COUNT(*) + 1 as rank 
                FROM usuario 
                WHERE id_rol = {$this->playerRole} 
                AND (
                    puntos_totales > (
                        SELECT puntos_totales 
                        FROM usuario 
                        WHERE nombre_usuario = '$username' AND id_rol = {$this->playerRole}
                    )
                    OR (
                        puntos_totales = (
                            SELECT puntos_totales 
                            FROM usuario 
                            WHERE nombre_usuario = '$username' AND id_rol = {$this->playerRole}
                        )
                        AND nombre_usuario < '$username'
                    )
                )";
        $result = $this->connection->query($sql);
        return $result[0]['rank'] ?? null;
    }
    public function getUserAvatar($username) {
        $sql = "SELECT foto FROM usuario WHERE nombre_usuario = '$username'";
        $result = $this->connection->query($sql);
        return $result[0]['foto'] ?? null;
    }
}