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
        "EditorController",
        "QuestionController"
    ],
    "guest" => [
        "LoginController",
        "RegistrationController"
    ]
];
