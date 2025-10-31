<?php

require_once __DIR__ . '/../enums/GameType.php';
require_once __DIR__ . '/../enums/GameResult.php';
require_once __DIR__ . '/PlayableQuestion.php';

class GameSessionModel {
    private $connection;
    private $currentGameId;
    private $player1Id;
    private $player2Id = null;
    private $questions = []; // array de PlayableQuestion
    private $player1Score = 0;
    private $player2Score = 0;
    private $currentQuestionIndex = 0;
    private $isFinished = false;


    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function createGame($userId) {
        // devuelve objeto currentGame con todos los atributos necesarios para la partida
        // además hay que persistir en la tabla Partida los campos: fecha, idJugador1, id_tipo_partida
        // Devuelve la partida creada
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

    public function getPlayableQuestionsForUser($userId) {
        // busco en la base 10 preguntas que el usuario no haya respondido nunca
        $sql = "SELECT 
        p.id AS idPregunta,
        p.enunciado,
        r.opcion_a,
        r.opcion_b,
        r.opcion_c,
        r.opcion_d,
        r.respuesta_correcta,
        c.nombre AS nombreCategoria,
        c.color AS colorCategoria
        FROM PREGUNTA p
        JOIN RESPUESTA r ON p.id_respuesta = r.id
        JOIN CATEGORIA c ON p.id_categoria = c.id
        WHERE p.id NOT IN (
            SELECT id_pregunta FROM RESPUESTA_USUARIO WHERE id_usuario = $userId
        )
        ORDER BY RAND()
        LIMIT 10";

        $result = $this->connection->query($sql);

        // creo objeto PreguntaCompleta o PlayableQuestion (necesito un modelo para esto)
        // creo un array del objeto anterior cruzando tablas pregunta, respuesta, categoria, respuesta_usuario(?)
        //devuelvo array
        $questions = [];
        $numero = 1;
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
                $numero++,
                $row['respuesta_correcta']
            );
        }

        $this->questions = $questions;
        return $questions;
    }

    public function saveResponseRelatedData($questionId, $wasCorrect) {
        $sqlSelect = "SELECT respuestas_totales, respuestas_correctas FROM PREGUNTA WHERE id = $questionId";
        $result = $this->connection->query($sqlSelect)[0];

        $totalResponses = $result['respuestas_totales'] + 1;
        $correctAnswers = $wasCorrect ? $result['respuestas_correctas'] + 1 : $result['respuestas_correctas'];
        $ratio = $correctAnswers / $totalResponses;

        // Luego, actualizar con los valores ya calculados
        $sqlUpdate = "UPDATE PREGUNTA
            SET respuestas_totales = $totalResponses,
                respuestas_correctas = $correctAnswers,
                ratio_aciertos = $ratio
            WHERE id = $questionId";

        $this->connection->query($sqlUpdate);
    }

    public function updateUserResponseData($submittedAnswer, $questionId, $wasCorrect) {
        $userId = $_SESSION["userId"];
        $puntosTotales = $_SESSION["totalScore"] + $_SESSION["currentGame"]["score"];
        $sqlUserPoints = "UPDATE USUARIO
            SET puntos_totales = $puntosTotales
            WHERE id = $userId";
        $this->connection->query($sqlUserPoints);

        $currentGameId = $_SESSION["currentGame"]["gameId"];
        $wasCorrect = $wasCorrect ? 1 : 0;
        $sqlUserResponse = "INSERT INTO RESPUESTA_USUARIO (
            id_usuario,
            id_pregunta,
            id_partida,
            opcion_elegida,
            fue_correcta
        ) VALUES (
            $userId,
            $questionId,
            $currentGameId,
            '$submittedAnswer',
            $wasCorrect
        )";
        $this->connection->query($sqlUserResponse);
    }

    public function storeGameResults() {
        // HAGO UN QUERY DIFERENTE DEL saveResponseRelatedData porque esto esta relacionado con el final de la partida y los puntajes finales
        $gameScore = $_SESSION["currentGame"]["score"];

        // sobre el resultado de la partida: de manera PROVISORIA la partida la ganamos o perdemos comparando con el puntaje del bot.
        $result = $this->generateBotScore() > $gameScore ? GameResult::LOST : GameResult::WON;
        $sql = "UPDATE PARTIDA
            SET puntaje_jugador1 = $gameScore,
                id_resultado = $result";

        $this->connection->query($sql);
    }

    public function generateBotScore() {
        $minCorrectAnswers = 2;
        $maxCorrectAnswers = 8;

        $botCorrectAnswers = rand($minCorrectAnswers, $maxCorrectAnswers);
        $pointsPerCorrectAnswer = 10;

        return $botCorrectAnswers * $pointsPerCorrectAnswer;
    }
    public function getNextQuestion(){
//        if ($currentIndex >= count($questions))
//            return null; // o lanzar excepción
        // actualizar pregunta_actual y la respuesta en respuesta_usuario
        // obtener de $_SESSION["currentGame"] la proxima pregunta.
        // devolver la prox pregunta (de tipo PlayableQuestion)
    }

}