<?php
require_once __DIR__ . '/../enums/Role.php';
class PlayerHomeModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getUserStats($username) {
        $playerRole = Role::PLAYER;
        $sqlUser = "SELECT id, puntos_totales FROM USUARIO WHERE nombre_usuario = '$username'";
        $userResult = $this->connection->query($sqlUser);

        if (empty($userResult)) {
            return [
                'user_name' => $username,
                'ranking' => '-',
                'score' => 0,
                'games_played' => 0,
                'games' => []
            ];
        }

        $user = $userResult[0];
        $userId = $user['id'];
        $score = $user['puntos_totales'];

        $sqlRank = "SELECT COUNT(*) + 1 AS rank
                FROM USUARIO
                WHERE id_rol = $playerRole
                AND (
                    puntos_totales > $score
                    OR (puntos_totales = $score AND nombre_usuario < '$username')
                )";
        $rankResult = $this->connection->query($sqlRank);
        $rank = $rankResult[0]['rank'] ?? '-';

        $sqlGames = "SELECT fecha, puntaje_jugador1 AS points, id
                 FROM PARTIDA
                 WHERE id_jugador1 = $userId
                 ORDER BY fecha DESC
                 LIMIT 5";
        $gamesRaw = $this->connection->query($sqlGames) ?? [];

        $games = [];
        foreach ($gamesRaw as $game) {
            $gameId = $game['id'];
            $sqlCorrect = "SELECT COUNT(*) AS correct
                       FROM RESPUESTA_USUARIO
                       WHERE id_partida = $gameId AND id_usuario = $userId AND fue_correcta = 1";
            $correct = $this->connection->query($sqlCorrect)[0]['correct'] ?? 0;

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
