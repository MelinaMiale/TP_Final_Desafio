<?php

class CiudadPaisModel {
    protected $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    protected function getOrCreatePais($pais) {
        $sql = "SELECT id FROM PAIS WHERE nombre = '$pais'";
        $result = $this->connection->query($sql);

        if ($result && count($result) > 0) {
            return $result[0]['id'];
        }

        $sql = "INSERT INTO PAIS (nombre) VALUES ('$pais')";
        $this->connection->query($sql);

        return $this->connection->getLastInsertId();
    }

    protected function getOrCreateCiudad($ciudad, $id_pais) {
        $sql = "SELECT id FROM CIUDAD WHERE nombre = '$ciudad' AND id_pais = $id_pais";
        $result = $this->connection->query($sql);

        if ($result && count($result) > 0) {
            return $result[0]['id'];
        }

        $sql = "INSERT INTO CIUDAD (nombre, id_pais) VALUES ('$ciudad', $id_pais)";
        $this->connection->query($sql);

        return $this->connection->getLastInsertId();
    }
}
