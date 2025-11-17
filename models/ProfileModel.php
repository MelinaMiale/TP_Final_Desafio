<?php

class ProfileModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getUserByUsername($username) {
        $sql = "
        SELECT 
            u.id,
            u.nombre_completo,
            u.anio_nacimiento,
            u.nombre_usuario,
            u.correo_electronico,
            u.foto,
            c.nombre AS ciudad,
            p.nombre AS pais,
            u.puntos_totales
        FROM USUARIO u
        JOIN CIUDAD c ON u.id_ciudad = c.id
        JOIN PAIS p ON c.id_pais = p.id
        WHERE u.nombre_usuario = '$username'
    ";

        $result = $this->connection->query($sql);
        // Si MyConnection->query() devuelve un array de filas:
        return $result ? $result[0] : null;
    }


    // Trae todas las partidas donde el usuario participó
    public function getPartidasByUser($userId) {

        $sqlGames = "SELECT fecha, puntaje_jugador1 AS points, id 
            FROM PARTIDA WHERE id_jugador1 = $userId 
                         ORDER BY fecha DESC LIMIT 5";

        $gamesRaw = $this->connection->query($sqlGames) ?? [];

        $partidas = [];
        foreach ($gamesRaw as $game) {
            $gameId = $game['id'];

            $sqlCorrect = "SELECT COUNT(*) AS correct FROM RESPUESTA_USUARIO 
                           WHERE id_partida = $gameId AND id_usuario = $userId AND fue_correcta = 1";

            $correct = $this->connection->query($sqlCorrect)[0]['correct'] ?? 0;

            $partidas[] = [
                'date' => date("d M Y",
                    strtotime($game['fecha'])),
                'correct' => $correct,
                'points' => $game['points'] ];

            }

        return $partidas;
    }

    private function getOrCreatePais($pais) {
        $sql = "SELECT id FROM PAIS WHERE nombre = '$pais'";
        $result = $this->connection->query($sql);
        if ($result && count($result) > 0) return $result[0]['id'];

        $sql = "INSERT INTO PAIS (nombre) VALUES ('$pais')";
        $this->connection->query($sql);
        return $this->connection->getLastInsertId();
    }

    private function getOrCreateCiudad($ciudad, $id_pais) {
        $sql = "SELECT id FROM CIUDAD WHERE nombre = '$ciudad' AND id_pais = $id_pais";
        $result = $this->connection->query($sql);
        if ($result && count($result) > 0) return $result[0]['id'];

        $sql = "INSERT INTO CIUDAD (nombre, id_pais) VALUES ('$ciudad', $id_pais)";
        $this->connection->query($sql);
        return $this->connection->getLastInsertId();
    }


}
