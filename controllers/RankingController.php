<?php

class RankingController {

    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function mostrar() {
        if (!isset($_SESSION["user_name"])) {
            header("Location: ?controller=login&method=loginForm");
            exit;
        }

        $pagina = $_GET['pagina'] ?? 1;
        $porPagina = 4;

        $data = $this->model->obtenerJugadores($pagina, $porPagina);
        $totalJugadores = $this->model->contarJugadores();
        $totalPaginas = ceil($totalJugadores / $porPagina);
        $paginas = range(1, $totalPaginas); // [1, 2, 3, 4]

        $saltar = ($pagina - 1) * $porPagina;
        foreach ($data as $index => &$jugador) {
            $jugador['posicion'] = $saltar + $index + 1;
            if (empty($jugador['foto'])) {
                $jugador['foto'] = 'defaultImagen.png';
            }
        }

        $posicionUsuario = $this->model->obtenerPosicionUsuario($_SESSION["user_name"]);
        
        // Obtener foto del usuario actual (o usar default)
        $fotoUsuario = $_SESSION["foto"] ?? 'defaultImagen.png';
        if (empty($fotoUsuario)) {
            $fotoUsuario = 'defaultImagen.png';
        }

        // Variables para la paginación
        $paginaAnterior = $pagina - 1;
        $paginaSiguiente = $pagina + 1;
        $esPrimeraPagina = ($pagina <= 1);
        $esUltimaPagina = ($pagina >= $totalPaginas);

        $this->renderer->render("ranking", [
            "user_name" => $_SESSION["user_name"],
            "puntos" => $_SESSION["puntos"] ?? 0,
            "posicion_usuario" => $posicionUsuario,
            "foto_usuario" => $fotoUsuario,
            "ranking" => $data,
            "pagina_actual" => $pagina,
            "total_paginas" => $paginas,
            "pagina_anterior" => $paginaAnterior,
            "pagina_siguiente" => $paginaSiguiente,
            "es_primera_pagina" => $esPrimeraPagina,
            "es_ultima_pagina" => $esUltimaPagina
        ]);
    }


}