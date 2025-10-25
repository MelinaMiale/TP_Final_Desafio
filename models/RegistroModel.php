<?php

class RegistroModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getUserByName($nombre_usuario) {
        $sql = "SELECT * FROM usuario WHERE nombre_usuario = '$nombre_usuario'";
        $result = $this->connection->query($sql);
        return $result ? $result[0] : null;
    }

    function getOrCreatePais($pais) {
        $sql = "SELECT id FROM pais WHERE nombre = '$pais'";
        $result = $this->connection->query($sql);
        if ($result && count($result) > 0) {
            return $result[0]['id'];
        }
        $sql = "INSERT INTO pais (nombre) VALUES ('$pais')";
        $this->connection->query($sql);
        return $this->connection->getLastInsertId();
    }

    function getOrCreateCiudad($ciudad, $id_pais) {
        $sql = "SELECT id FROM ciudad WHERE nombre = '$ciudad' AND id_pais = $id_pais";
        $result = $this->connection->query($sql);
        if ($result && count($result) > 0) {
            return $result[0]['id'];
        }
        $sql = "INSERT INTO ciudad (nombre, id_pais) VALUES ('$ciudad', $id_pais)";
        $this->connection->query($sql);
        return $this->connection->getLastInsertId();
    }

    public function crearUsuario($nombre_completo, $anio_nacimiento, $sexo, $nombre_usuario, $email, $password, $ciudad, $pais) {
        $id_sexo = ($sexo == "Femenino") ? 1 : (($sexo == "Masculino") ? 2 : 3);
        $id_rol = 3;
        $id_pais = $this->getOrCreatePais($pais);
        $id_ciudad = $this->getOrCreateCiudad($ciudad, $id_pais);
        $sql = "INSERT INTO usuario (nombre_completo, anio_nacimiento, correo_electronico, contrasenia, nombre_usuario, puntos_totales, id_sexo, id_rol, id_ciudad)
            VALUES ('$nombre_completo', $anio_nacimiento, '$email', '$password', '$nombre_usuario', 0, $id_sexo, $id_rol, $id_ciudad)";
        $this->connection->query($sql);
    }
}
