<?php
include_once("MyConnection.php");
include_once("MustacheRenderer.php");
include_once("Router.php");
include_once("helper/MailManager.php");
include_once(__DIR__ . '/../controllers/LoginController.php');
include_once(__DIR__ . '/../controllers/RegistrationController.php');
include_once(__DIR__ . '/../controllers/PlayerhomeController.php');
include_once(__DIR__ . '/../controllers/GameController.php');
include_once(__DIR__ . '/../controllers/ProfileController.php');
include_once(__DIR__ . '/../models/LoginModel.php');
include_once(__DIR__ . '/../models/RegistrationModel.php');
include_once(__DIR__ . '/../models/PlayerHomeModel.php');
include_once(__DIR__ . '/../models/GameSessionModel.php');
include_once(__DIR__ . '/../models/ProfileModel.php');
include_once(__DIR__ . '/../vendor/mustache/src/Mustache/Autoloader.php');
include_once(__DIR__ . '/../controllers/RankingController.php');
include_once(__DIR__ . '/../models/RankingModel.php');
include_once(__DIR__ . '/../controllers/ReportquestionController.php');
include_once(__DIR__ . '/../controllers/AdminController.php');
include_once(__DIR__ . '/../controllers/EditorController.php');
include_once(__DIR__ . '/../controllers/QuestionController.php');
include_once(__DIR__ . '/../controllers/CategoryController.php');
include_once(__DIR__ . '/../models/ReportQuestionModel.php');
include_once(__DIR__ . '/../helper/AuthorizationManager.php');
include_once(__DIR__ . '/../models/AdminModel.php');
include_once(__DIR__ . '/../models/EditorModel.php');
include_once(__DIR__ . '/../models/QuestionModel.php');
include_once(__DIR__ . '/../models/CategoryModel.php');


class ConfigFactory {
    private $objects;
    private $connection;
    private $renderer;

    public function __construct() {
        $config = parse_ini_file("config/db_config");

        $this->connection = new MyConnection(
            $config["server"],
            $config["user"],
            $config["pass"],
            $config["database"]
        );

        $this->renderer = new MustacheRenderer("views");

        $permissions = include(__DIR__ . "/../config/permissions.php");
        $authorizationManager = new AuthorizationManager($permissions);
        $this->objects["auth"] = $authorizationManager;
        $this->objects["router"] = new Router($this, "LoginController", "loginForm", $authorizationManager);

        $this->objects["LoginController"] =
            new LoginController(new LoginModel($this->connection), $this->renderer);

        $this->objects["RegistrationController"] =
            new RegistrationController(new RegistrationModel($this->connection), $this->renderer);

        $this->objects["RankingController"] =
            new RankingController(new RankingModel($this->connection), $this->renderer);
      
        $this->objects["PlayerhomeController"] =
            new PlayerhomeController(new PlayerHomeModel($this->connection), $this->renderer);

        $this->objects["GameController"] =
            new GameController(new GameSessionModel($this->connection), $this->renderer);

        $this->objects["ProfileController"] =
            new ProfileController(new ProfileModel($this->connection), $this->renderer);

        $this->objects["ReportquestionController"] =
            new ReportquestionController(new ReportQuestionModel($this->connection), $this->renderer);

        $this->objects["AdminController"] =
            new AdminController(new AdminModel($this->connection), $this->renderer);

        $this->objects["EditorController"] =
            new EditorController(new EditorModel($this->connection), $this->renderer);

        $this->objects["QuestionController"] =
            new QuestionController(new QuestionModel($this->connection), $this->renderer);

        $this->objects["CategoryController"] =
            new CategoryController(new CategoryModel($this->connection), $this->renderer);
    }

    public function get($objectName) {
        return $this->objects[$objectName];
    }
}
