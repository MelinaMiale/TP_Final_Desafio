<?php

class RegistroModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getUserByName($user) {
        $sql = "SELECT * FROM usuarios WHERE usuario = '$user'";
        $result = $this->connection->query($sql);
        return $result ? $result[0] : null;
    }

    public function crearUsuario($user, $password) {
        $sql = "INSERT INTO usuarios (usuario, password) VALUES ('$user', '$password')";
        $this->connection->query($sql);
    }
}
