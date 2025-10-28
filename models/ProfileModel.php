<?php

class ProfileModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getUserByUsername($username) {
        $sql = "SELECT u.nombre_completo, u.anio_nacimiento, u.nombre_usuario, u.correo_electronico, 
                       c.nombre AS ciudad, p.nombre AS pais, u.puntos_totales
                FROM usuario u
                JOIN ciudad c ON u.id_ciudad = c.id
                JOIN pais p ON c.id_pais = p.id
                WHERE u.nombre_usuario = '$username'";
        $result = $this->connection->query($sql);
        return $result ? $result[0] : null;
    }
}
