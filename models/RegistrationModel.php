<?php
require_once __DIR__ . "/CiudadPaisModel.php";

class RegistrationModel extends CiudadPaisModel {

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getUserByName($nombre_usuario) {
        $sql = "SELECT * FROM USUARIO WHERE nombre_usuario = '$nombre_usuario'";
        $result = $this->connection->query($sql);
        return $result ? $result[0] : null;
    }

    public function createUser($nombre_completo, $anio_nacimiento, $sexo, $nombre_usuario, $cleanEmail, $password, $ciudad, $pais, $nombreFoto) {
        $confirmationCode = bin2hex(random_bytes(16));
        $id_sexo = ($sexo == "Femenino") ? 1 : (($sexo == "Masculino") ? 2 : 3);
        $id_rol = 3;
        $id_pais = $this->getOrCreatePais($pais);
        $id_ciudad = $this->getOrCreateCiudad($ciudad, $id_pais);
        $sql = "INSERT INTO USUARIO (nombre_completo, anio_nacimiento, correo_electronico, contrasenia, nombre_usuario, puntos_totales, id_sexo, id_rol, id_ciudad, foto, codigo_verificacion) 
                VALUES ('$nombre_completo', $anio_nacimiento, '$cleanEmail', '$password', '$nombre_usuario', 0, $id_sexo, $id_rol, $id_ciudad, '$nombreFoto', '$confirmationCode')";
        
        $this->connection->query($sql);

        return $confirmationCode;
    }

    public function validateUser($confirmationCode) {
        $sql = "SELECT id FROM USUARIO WHERE codigo_verificacion = '$confirmationCode'";
        $result = $this->connection->query($sql);

        if ($result && count($result) > 0) {
            $userId = $result[0]['id'];

            $updateSql = "UPDATE USUARIO 
                      SET cuenta_verificada = 1, codigo_verificacion = NULL 
                      WHERE id = $userId";
            $this->connection->query($updateSql);
            return true;
        }
        return false;
    }

}
