<?php

class UserLevelManager {
    private $connection;
    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getUserResponseData($userId) {
        $sql = "SELECT COUNT(*) AS totalResponses, SUM(CASE WHEN fue_correcta = 1 THEN 1 ELSE 0 END) AS correctResponses
                FROM RESPUESTA_USUARIO
                WHERE id_usuario = $userId";

        return $this->connection->query($sql);
    }

    public function calculateAndSetUserLevel($userId) {
        $userResponseData = $this->getUserResponseData($userId);

        $totalResponses = $userResponseData['totalResponses'];
        $correctResponses = $userResponseData['correctResponses'];

        if ($totalResponses < 10) {
            $ratio = 0.5;
        } else {
            $ratio = $correctResponses / $totalResponses;
        }

        // todo: si persistiera este valor en la tabla usuario, y utilizaría este bloque de codigo para detectar el cambio y avisarselo. incentivarlo a que siga jugando. reveer! por lo pronto no se usa.
        if ($ratio >= 0.7) {
            $level = "superSayayin";
        } elseif ($ratio >= 0.3 && $ratio < 0.7) {
            $level = "guerrero";
        } else {
            $level = "pocoKi";
        }

        return [
            "ratio" => $ratio,
            "user_level" => $level
//            "totalResponses" => $totalResponses,
//            "correctResponses" => $correctResponses
        ];
    }
}