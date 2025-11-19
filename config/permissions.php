<?php

return [
    Role::PLAYER => [
        "HomeController",
        "GameController",
        "ProfileController",
        "RankingController",
        "ReportquestionController"
    ],
    Role::ADMIN => [
        "AdminController"
    ],
    Role::EDITOR => [
        "EditorController"
    ],
    "guest" => [
        "LoginController",
        "RegistrationController"
    ]
];
