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
        FROM usuario u
        JOIN ciudad c ON u.id_ciudad = c.id
        JOIN pais p ON c.id_pais = p.id
        WHERE u.nombre_usuario = '$username'
    ";

        $result = $this->connection->query($sql);
        // Si MyConnection->query() devuelve un array de filas:
        return $result ? $result[0] : null;
    }


    // Trae todas las partidas donde el usuario participó
    public function getPartidasByUser($userId) {
        $sql = "
        SELECT 
            p.id,
            p.fecha,
            p.puntaje_jugador1,
            p.puntaje_jugador2,
            p.id_resultado,
            p.id_tipo_partida,
            u1.nombre_usuario AS jugador1,
            u2.nombre_usuario AS jugador2,
            r.nombre AS resultado,
            t.nombre AS tipo_partida
        FROM partida p
        JOIN usuario u1 ON p.id_jugador1 = u1.id
        LEFT JOIN usuario u2 ON p.id_jugador2 = u2.id
        JOIN resultado r ON p.id_resultado = r.id
        JOIN tipo_partida t ON p.id_tipo_partida = t.id
        WHERE p.id_jugador1 = $userId OR p.id_jugador2 = $userId
        ORDER BY p.fecha DESC
        LIMIT 5
    ";

        $partidas = $this->connection->query($sql);

        if (!$partidas) {
            return [];
        }

        foreach ($partidas as &$p) {
            // Si la partida es contra la IA
            if ($p['id_tipo_partida'] == 1) {
                $p['jugador2'] = 'IA';
                $p['jugador2_iniciales'] = 'IA';
            } else {
                $p['jugador2_iniciales'] = $p['jugador2'] ? strtoupper(substr($p['jugador2'], 0, 2)) : '??';
            }

            // Iniciales del jugador 1
            $p['jugador1_iniciales'] = strtoupper(substr($p['jugador1'], 0, 2));

            // Determinar ganadores
            $p['es_ganador_j1'] = ($p['id_resultado'] == 2);
            $p['es_ganador_j2'] = ($p['id_resultado'] == 3);
        }

        return $partidas;
    }

    private function getOrCreatePais($pais) {
        $sql = "SELECT id FROM pais WHERE nombre = '$pais'";
        $result = $this->connection->query($sql);
        if ($result && count($result) > 0) return $result[0]['id'];

        $sql = "INSERT INTO pais (nombre) VALUES ('$pais')";
        $this->connection->query($sql);
        return $this->connection->getLastInsertId();
    }

    private function getOrCreateCiudad($ciudad, $id_pais) {
        $sql = "SELECT id FROM ciudad WHERE nombre = '$ciudad' AND id_pais = $id_pais";
        $result = $this->connection->query($sql);
        if ($result && count($result) > 0) return $result[0]['id'];

        $sql = "INSERT INTO ciudad (nombre, id_pais) VALUES ('$ciudad', $id_pais)";
        $this->connection->query($sql);
        return $this->connection->getLastInsertId();
    }


}
