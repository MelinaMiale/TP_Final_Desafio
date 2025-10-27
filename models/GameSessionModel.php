<?php

class GameSessionModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function createGame() {
        // devuelve objeto currentGame con todos los atributos necesarios para la partida
        // además hay que persistir en la tabla Partida los campos: fecha, idJugador1, id_tipo_partida
        // Devuelve la partida creada
    }
    public function getPlayableQuestionsForUser($userId) {
        // creo objeto PreguntaCompleta o PlayableQuestion (necesito un modelo para esto)
        // creo un array del objeto anterior cruzando tablas pregunta, respuesta, categoria, respuesta_usuario(?)
        //devuelvo array
    }

    public function saveResponseRelatedData() {}
    public function generateBotScore(){}
}