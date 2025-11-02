<?php
require_once __DIR__ . '/../enums/Role.php';
class HomeModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getUserStats($username) {
        $sqlUser = "SELECT id, puntos_totales FROM USUARIO WHERE nombre_usuario = '$username'";
        $user = $this->connection->query($sqlUser)[0];
        $userId = $user['id'];
        $score = $user['puntos_totales'];

        $playerRole = ROLE::PLAYER;
        $sqlRank = "SELECT COUNT(*) + 1 AS rank
                    FROM USUARIO
                    WHERE id_rol = $playerRole
                    AND (
                        puntos_totales > $score
                        OR (puntos_totales = $score AND nombre_usuario < '$username')
                    )";
        $rank = $this->connection->query($sqlRank)[0]['rank'];

        $sqlGames = "SELECT fecha, puntaje_jugador1 AS points, id
                     FROM PARTIDA
                     WHERE id_jugador1 = $userId
                     ORDER BY fecha DESC
                     LIMIT 5";
        $gamesRaw = $this->connection->query($sqlGames);

        $games = [];
        foreach ($gamesRaw as $game) {
            $gameId = $game['id'];
            $sqlCorrect = "SELECT COUNT(*) AS correct
                           FROM RESPUESTA_USUARIO
                           WHERE id_partida = $gameId AND id_usuario = $userId AND fue_correcta = 1";
            $correct = $this->connection->query($sqlCorrect)[0]['correct'];

            $games[] = [
                'date' => date("d M Y", strtotime($game['fecha'])),
                'correct' => $correct,
                'points' => $game['points']
            ];
        }

        return [
            'user_name' => $username,
            'ranking' => $rank,
            'score' => $score,
            'games_played' => count($gamesRaw),
            'games' => $games
        ];
    }
}
