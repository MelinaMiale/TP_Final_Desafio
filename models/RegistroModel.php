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
    public function crearUsuario($nombre_completo, $anio_nacimiento, $sexo, $nombre_usuario, $email, $password, $ubicacion) {
        $id_sexo = ($sexo == "Femenino") ? 1 : (($sexo == "Masculino") ? 2 : 3);
        $id_rol = 3;
        $id_ciudad = 1;
        $sql = "INSERT INTO USUARIO (nombre_completo, anio_nacimiento, correo_electronico, contrasenia, nombre_usuario, puntos_totales, id_sexo, id_rol, id_ciudad) 
                VALUES ('$nombre_completo', $anio_nacimiento, '$email', '$password', '$nombre_usuario', 0, $id_sexo, $id_rol, $id_ciudad)";
        
        $this->connection->query($sql);
    }
}
