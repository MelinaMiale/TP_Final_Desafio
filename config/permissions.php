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
        "QuestionController",
        "CategoryController"
    ],
    "guest" => [
        "LoginController",
        "RegistrationController"
    ]
];
