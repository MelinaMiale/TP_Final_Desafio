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

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filterCountry = isset($_GET['country']) && $_GET['country'] !== 'all' ? (int)$_GET['country'] : null;
        $filterTime = isset($_GET['time']) ? $_GET['time'] : 'historico';

        $perPage = 4;

        $players = $this->model->getPlayers($page, $perPage, $search, $filterCountry, $filterTime);
        $totalPlayers = $this->model->countPlayers($search, $filterCountry);

        $totalPages = $totalPlayers > 0 ? ceil($totalPlayers / $perPage) : 1;
        $pages = range(1, $totalPages);

        foreach ($players as $index => &$player) {
            if (empty($player['avatar'])) {
                $player['avatar'] = 'defaultImagen.png';
            }
        }

        $currentUser = $_SESSION["user_name"];
        $userRank = $this->model->getUserRank($currentUser, $filterCountry, $filterTime);
        $userScore = $this->model->getUserScore($currentUser, $filterTime);
        $userAvatar = $this->model->getUserAvatar($currentUser);
        if (empty($userAvatar)) $userAvatar = 'defaultImagen.png';

        $previousPage = $page - 1;
        $nextPage = $page + 1;
        $isFirstPage = ($page <= 1);
        $isLastPage = ($page >= $totalPages);

        $countries = $this->model->getAllCountries();

        $this->renderer->render("ranking", [
            "user_name" => $currentUser,
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
            "countries" => $countries,
            "filter_country" => $filterCountry,
            "filter_time" => $filterTime
        ]);
    }
}