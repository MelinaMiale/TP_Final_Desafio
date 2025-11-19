<?php

require_once __DIR__ . '/../enums/GameType.php';
require_once __DIR__ . '/../enums/GameResult.php';
require_once __DIR__ . '/PlayableQuestion.php';
require_once __DIR__ . '/../enums/QuestionStatus.php';
require_once __DIR__ . '/DifficultyManager.php';
require_once __DIR__ . '/../enums/QuestionDifficulty.php';
require_once __DIR__ . '/UserLevelManager.php';
require_once __DIR__ . '/../enums/UserLevel.php';


class GameSessionModel {
    private $connection;
    private $currentGameId;
    private $player1Id;
    private $player2Id = null;
    private $questions = [];
    private $player1Score = 0;
    private $player2Score = 0;
    private $currentQuestionIndex = 0;
    private $isFinished = false;


    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function createGame($userId) {
        $this->setUserLevel($userId);
        $this->player1Id = $userId;

        $gameType = GameType::VS_BOT;
        $initialResult = GameResult::LOST;

        $sql = "INSERT INTO PARTIDA (fecha, puntaje_jugador1, id_jugador1, id_tipo_partida, id_resultado)
        VALUES (NOW(), 0, $userId, $gameType, $initialResult)";

        $this->connection->query($sql);
        $this->currentGameId = $this->connection->getLastInsertId();

        return (object)[
            'id' => $this->currentGameId
        ];
    }

    private function setUserLevel($userId) {
        $userLevelManager = new UserLevelManager($this->getConnection());
        $userLevelData = $userLevelManager->calculateAndSetUserLevel($userId);
        $_SESSION["userLevel"] = $userLevelData;
    }

    public function getPlayableQuestionsForUser($userId) {
        $approvedStatus = QuestionStatus::APPROVED;
        $userLevel = $_SESSION["userLevel"]["user_level"];

        $userLevelManager = new UserLevelManager($this->getConnection());
        $questionDistribution = $userLevelManager->getQuestionDistributionByUserLevel($userLevel);

        $questions = [];
        $numero = 1;

        foreach ($questionDistribution as $questionBlock) {
            $difficulty = $questionBlock['difficulty'];
            $count = $questionBlock['count'];

            $sql = "SELECT 
                    p.id AS idPregunta,
                    p.enunciado,
                    r.opcion_a,
                    r.opcion_b,
                    r.opcion_c,
                    r.opcion_d,
                    r.respuesta_correcta,
                    c.nombre AS nombreCategoria,
                    c.color AS colorCategoria,
                    p.id_categoria AS id_categoria,
                    p.respuestas_correctas,
                    p.respuestas_totales,
                    p.ratio_aciertos,
                    p.dificultad_actual
                FROM PREGUNTA p
                JOIN RESPUESTA r ON p.id_respuesta = r.id
                JOIN CATEGORIA c ON p.id_categoria = c.id
                LEFT JOIN AUDITORIA_PREGUNTA ap ON p.id = ap.id_pregunta
                WHERE p.dificultad_actual = $difficulty
                  AND p.id NOT IN (
                      SELECT id_pregunta FROM RESPUESTA_USUARIO WHERE id_usuario = $userId
                  )
                  AND (ap.id_pregunta IS NULL OR ap.estado_destino = $approvedStatus)
                ORDER BY RAND()
                LIMIT $count";

            $result = $this->connection->query($sql);

            foreach ($result as $row) {
                $questions[] = new PlayableQuestion(
                    $row['idPregunta'],
                    $row['enunciado'],
                    $row['opcion_a'],
                    $row['opcion_b'],
                    $row['opcion_c'],
                    $row['opcion_d'],
                    $row['nombreCategoria'],
                    $row['colorCategoria'],
                    $row['id_categoria'],
                    $numero++,
                    $row['respuesta_correcta'],
                    $row['respuestas_correctas'],
                    $row['respuestas_totales'],
                    $row['ratio_aciertos'],
                    $row['dificultad_actual']
                );
            }
        }

        shuffle($questions);
        $this->questions = $questions;
        return $questions;
    }

    public function updateUserResponseData($submittedAnswer, $questionId, $wasCorrect) {
        $userId = $_SESSION["userId"];
        $currentGameId = $_SESSION["currentGame"]["gameId"];
        $wasCorrect = $wasCorrect ? 1 : 0;

        $puntosTotales = $_SESSION["totalScore"] + $_SESSION["currentGame"]["score"];
        $sqlUserPoints = "UPDATE USUARIO
        SET puntos_totales = $puntosTotales
        WHERE id = $userId";
        $this->connection->query($sqlUserPoints);

        $sqlUpdateResponse = "UPDATE RESPUESTA_USUARIO
        SET opcion_elegida = '$submittedAnswer',
            fue_correcta = $wasCorrect
        WHERE id_usuario = $userId
          AND id_partida = $currentGameId
          AND id_pregunta = $questionId";
        $this->connection->query($sqlUpdateResponse);
    }

    public function registerQuestionAssignment($userId, $questionId, $gameId) {
        $sql = "INSERT INTO RESPUESTA_USUARIO (id_usuario, id_pregunta, id_partida, opcion_elegida, fue_correcta) VALUES ( $userId, $questionId, $gameId, NULL, NULL)";
        $this->connection->query($sql);
    }

    public function storeGameResults($currentGameId) {
        $gameScore = $_SESSION["currentGame"]["score"];

        // sobre el resultado de la partida: cuando implementemos lo del bot retomaremos esta parte, por ahora las partidas se pierden.
        $result = GameResult::LOST;// $this->generateBotScore() > $gameScore ? GameResult::LOST : GameResult::WON;
        $sql = "UPDATE PARTIDA
            SET puntaje_jugador1 = $gameScore,
                id_resultado = $result
                WHERE id = $currentGameId";

        $this->connection->query($sql);
    }

    public function resetUserQuestionHistory() {
        $userId = $_SESSION["userId"];

        $sqlDeleteResponses = "DELETE FROM RESPUESTA_USUARIO WHERE id_usuario = $userId";
        $this->connection->query($sqlDeleteResponses);

        $sqlDeleteGames = "DELETE FROM PARTIDA WHERE id_jugador1 = $userId";
        $this->connection->query($sqlDeleteGames);

        $sqlResetPoints = "UPDATE USUARIO SET puntos_totales = 0 WHERE id = $userId";
        $this->connection->query($sqlResetPoints);
    }

//    public function generateBotScore() {
//        $minCorrectAnswers = 2;
//        $maxCorrectAnswers = 8;
//
//        $botCorrectAnswers = rand($minCorrectAnswers, $maxCorrectAnswers);
//        $pointsPerCorrectAnswer = 10;
//
//        return $botCorrectAnswers * $pointsPerCorrectAnswer;
//    }

}