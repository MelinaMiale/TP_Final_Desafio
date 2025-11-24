<?php

class AdminController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function displayAdmin() {
        if (!isset($_SESSION["user_name"])) {
            header("Location: ?controller=login&method=loginForm");
            exit;
        }

        $username = $_SESSION["user_name"];
        $datos = $this->model->getAdminStats();
        $datos['admin_name'] = $username;

        $datos['datosDay'] = json_encode($this->model->getNuevosJugadoresPorPeriodo('day'));
        $datos['datosWeek'] = json_encode($this->model->getNuevosJugadoresPorPeriodo('week'));
        $datos['datosMonth'] = json_encode($this->model->getNuevosJugadoresPorPeriodo('month'));
        $datos['datosYear'] = json_encode($this->model->getNuevosJugadoresPorPeriodo('year'));

        $datos['partidasDay'] = json_encode($this->model->getPartidasPorPeriodo('day'));
        $datos['partidasWeek'] = json_encode($this->model->getPartidasPorPeriodo('week'));
        $datos['partidasMonth'] = json_encode($this->model->getPartidasPorPeriodo('month'));
        $datos['partidasYear'] = json_encode($this->model->getPartidasPorPeriodo('year'));

        $datos['preguntasDay'] = json_encode($this->model->getNuevasPreguntasPorPeriodo('day'));
        $datos['preguntasWeek'] = json_encode($this->model->getNuevasPreguntasPorPeriodo('week'));
        $datos['preguntasMonth'] = json_encode($this->model->getNuevasPreguntasPorPeriodo('month'));
        $datos['preguntasYear'] = json_encode($this->model->getNuevasPreguntasPorPeriodo('year'));

        $datos['dificultadDay'] = json_encode($this->model->getPrequntasPorDificultad('day'));
        $datos['dificultadWeek'] = json_encode($this->model->getPrequntasPorDificultad('week'));
        $datos['dificultadMonth'] = json_encode($this->model->getPrequntasPorDificultad('month'));
        $datos['dificultadYear'] = json_encode($this->model->getPrequntasPorDificultad('year'));
        $datos['dificultadAll'] = json_encode($this->model->getPrequntasPorDificultad('all'));
        $this->renderer->render('admin', $datos);
    }

    public function displayAdminUsers() {
        if (!isset($_SESSION["user_name"])) {
            header("Location: ?controller=login&method=loginForm");
            exit;
        }

        $username = $_SESSION["user_name"];
        $datos = $this->model->getAdminStats();
        $datos['admin_name'] = $username;

        $datos['usuarios_estadisticas'] = $this->model->getUsuariosConEstadisticas();

        $datos['paisDay'] = json_encode($this->model->getUsuariosPorPais('day'));
        $datos['paisWeek'] = json_encode($this->model->getUsuariosPorPais('week'));
        $datos['paisMonth'] = json_encode($this->model->getUsuariosPorPais('month'));
        $datos['paisYear'] = json_encode($this->model->getUsuariosPorPais('year'));

        $datos['sexoDay'] = json_encode($this->model->getUsuariosPorSexo('day'));
        $datos['sexoWeek'] = json_encode($this->model->getUsuariosPorSexo('week'));
        $datos['sexoMonth'] = json_encode($this->model->getUsuariosPorSexo('month'));
        $datos['sexoYear'] = json_encode($this->model->getUsuariosPorSexo('year'));

        $datos['edadDay'] = json_encode($this->model->getUsuariosPorEdad('day'));
        $datos['edadWeek'] = json_encode($this->model->getUsuariosPorEdad('week'));
        $datos['edadMonth'] = json_encode($this->model->getUsuariosPorEdad('month'));
        $datos['edadYear'] = json_encode($this->model->getUsuariosPorEdad('year'));

        $this->renderer->render("admin2", $datos);
    }

}