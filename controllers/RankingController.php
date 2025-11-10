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

        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        } else {
            $page = 1;
        }

        $perPage = 4;

        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        } else {
            $search = '';
        }

        $players = $this->model->getPlayers($page, $perPage, $search);
        $totalPlayers = $this->model->countPlayers($search);
        $totalPages = ceil($totalPlayers / $perPage);
        $pages = range(1, $totalPages);

        foreach ($players as $index => &$player) {
            if (empty($player['avatar'])) {
                $player['avatar'] = 'defaultImagen.png';
            }
        }

        $userRank = $this->model->getUserRank($_SESSION["user_name"]);

        $userAvatar = $this->model->getUserAvatar($_SESSION["user_name"]);
        if (empty($userAvatar)) {
            $userAvatar = 'defaultImagen.png';
        }

        $userScore = $this->model->getUserScore($_SESSION["user_name"]);
        if (empty($userScore)) {
            $userScore = 0;
        }

        $previousPage = $page - 1;
        $nextPage = $page + 1;
        $isFirstPage = ($page <= 1);
        $isLastPage = ($page >= $totalPages);

        $countries = $this->model->getAllCountries();

        $this->renderer->render("ranking", [
            "user_name" => $_SESSION["user_name"],
            "score" => $userScore,
            "user_rank" => $userRank,
            "user_avatar" => $userAvatar,
            "ranking" => $players,
            "current_page" => $page,
            "total_pages" => $pages,
            "previous_page" => $previousPage,
            "next_page" => $nextPage,
            "is_first_page" => $isFirstPage,
            "is_last_page" => $isLastPage,
            "search_term" => $search,
            "countries" => $countries
        ]);
    }

}