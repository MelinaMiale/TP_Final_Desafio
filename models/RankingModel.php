<?php

class RankingModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function obtenerJugadores($pagina = 1, $porPagina = 4) {
        $saltar = ($pagina - 1) * $porPagina;
        $sql = "SELECT u.nombre_usuario, 
                   u.puntos_totales,
                   u.foto 
            FROM usuario u 
            WHERE u.id_rol = 3
            ORDER BY u.puntos_totales DESC, u.nombre_usuario ASC
            LIMIT $porPagina OFFSET $saltar";
        return $this->connection->query($sql);
    }
    public function contarJugadores() {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE id_rol = 3";
        $result = $this->connection->query($sql);
        return $result[0]['total'];
    }

    public function obtenerPosicionUsuario($nombreUsuario) {
        $sql = "SELECT COUNT(*) + 1 as posicion 
                FROM usuario 
                WHERE id_rol = 3 
                AND (
                    puntos_totales > (
                        SELECT puntos_totales 
                        FROM usuario 
                        WHERE nombre_usuario = '$nombreUsuario' AND id_rol = 3
                    )
                    OR (
                        puntos_totales = (
                            SELECT puntos_totales 
                            FROM usuario 
                            WHERE nombre_usuario = '$nombreUsuario' AND id_rol = 3
                        )
                        AND nombre_usuario < '$nombreUsuario'
                    )
                )";
        $result = $this->connection->query($sql);
        return $result[0]['posicion'] ?? null;
    }
}