<?php
require_once __DIR__ . '/../enums/Role.php';
require_once __DIR__ . '/RankingModel.php';

class PlayerHomeModel {
    private $connection;
    private $rankingModel;

    public function __construct($connection) {
        $this->connection = $connection;
        $this->rankingModel = new RankingModel($connection);
    }

    public function getUserStats($username) {
        $userId = $_SESSION["userId"];
        $score = $this->rankingModel->getUserScore($username);

        if ($score == 0) {
            return $this->emptyStats($username);
        }

        $rank = $this->rankingModel->getUserRank($username);

        $games = $this->getRecentGames($userId);

        return [
            'user_name' => $username,
            //'avatar' => $avatar,
            'ranking' => $rank,
            'score' => $score,
            'games_played' => count($games),
            'games' => $games
        ];
    }

    private function getRecentGames($userId) {
        $sql = "SELECT fecha, puntaje_jugador1 AS points, id
                FROM PARTIDA
                WHERE id_jugador1 = $userId
                ORDER BY fecha DESC
                LIMIT 5";
        $gamesRaw = $this->connection->query($sql) ?? [];

        $games = [];
        foreach ($gamesRaw as $game) {
            $correct = $this->getCorrectAnswersCount($game['id'], $userId);
            $games[] = [
                'date' => date("d M Y", strtotime($game['fecha'])),
                'time' => date("H:i", strtotime($game['fecha'])),
                'correct' => $correct,
                'points' => $game['points']
            ];
        }
        return $games;
    }

    private function getCorrectAnswersCount($gameId, $userId) {
        $sql = "SELECT COUNT(*) AS correct
                FROM RESPUESTA_USUARIO
                WHERE id_partida = $gameId AND id_usuario = $userId AND fue_correcta = 1";
        $result = $this->connection->query($sql);
        return $result[0]['correct'] ?? 0;
    }

    private function emptyStats($username) {
        return [
            'user_name' => $username,
            'ranking' => '-',
            'score' => 0,
            'games_played' => 0,
            'games' => []
        ];
    }

    public function turnPlayerIntoEditor() {
        $editorRole = Role::EDITOR;
        $userId = $_SESSION['userId'];
        $updateUserQuery = "UPDATE USUARIO
                SET id_rol = $editorRole
                WHERE id = $userId";
        $this->connection->query($updateUserQuery);
    }
}