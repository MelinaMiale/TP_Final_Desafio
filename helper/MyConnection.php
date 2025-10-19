<?php

class MyConnection{
    private $connection;

    public function __construct($server, $user, $pass, $database) {
        $this->connection = new mysqli($server, $user, $pass, $database);
        if ($this->connection->error) { die("Error en la conexión: " . $this->connection->error); }
    }
    public function query($sql) {
        $result = $this->connection->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return null;
    }
}