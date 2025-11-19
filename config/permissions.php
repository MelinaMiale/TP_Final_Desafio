<?php

return [
    Role::PLAYER => [
        "PlayerhomeController",
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
