<?php

class RankingController {

    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function display() {
        if (!isset($_SESSION["user_name"])) {
            header("Location: ?controller=login&method=loginForm");
            exit;
        }

        $page = $_GET['page'] ?? 1;
        $perPage = 4;

        $players = $this->model->getPlayers($page, $perPage);
        $totalPlayers = $this->model->countPlayers();
        $totalPages = ceil($totalPlayers / $perPage);
        $pages = range(1, $totalPages);

        $offset = ($page - 1) * $perPage;
        foreach ($players as $index => &$player) {
            $player['rank'] = $offset + $index + 1;
            if (empty($player['avatar'])) {
                $player['avatar'] = 'defaultImagen.png';
            }
        }

        $userRank = $this->model->getUserRank($_SESSION["user_name"]);

        $userAvatar = $_SESSION["foto"] ?? 'defaultImagen.png';
        if (empty($userAvatar)) {
            $userAvatar = 'defaultImagen.png';
        }

        $previousPage = $page - 1;
        $nextPage = $page + 1;
        $isFirstPage = ($page <= 1);
        $isLastPage = ($page >= $totalPages);

        $this->renderer->render("ranking", [
            "user_name" => $_SESSION["user_name"],
            "score" => $_SESSION["score"] ?? 0,
            "user_rank" => $userRank,
            "user_avatar" => $userAvatar,
            "ranking" => $players,
            "current_page" => $page,
            "total_pages" => $pages,
            "previous_page" => $previousPage,
            "next_page" => $nextPage,
            "is_first_page" => $isFirstPage,
            "is_last_page" => $isLastPage
        ]);
    }
}