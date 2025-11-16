<?php

class LoginModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getUserByUserNameAndPassword($user, $password) {
        $sql = "SELECT * FROM USUARIO WHERE nombre_usuario = '$user' AND contrasenia = '$password'";
        $result = $this->connection->query($sql);
        return $result ? $result[0] : null;
    }
}
