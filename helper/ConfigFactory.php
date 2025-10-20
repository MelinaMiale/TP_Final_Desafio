<?php
include_once("MyConnection.php");
include_once("MustacheRenderer.php");
include_once("Router.php");
include_once(__DIR__ . '/../controllers/LoginController.php');
include_once(__DIR__ . '/../controllers/RegistrationController.php');
include_once(__DIR__ . '/../models/LoginModel.php');
include_once(__DIR__ . '/../models/RegistroModel.php');
include_once(__DIR__ . '/../vendor/mustache/src/Mustache/Autoloader.php');


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

        $this->objects["router"] = new Router($this, "LoginController", "loginForm");

        $this->objects["LoginController"] =
            new LoginController(new LoginModel($this->connection), $this->renderer);

        $this->objects["RegistrationController"] =
            new RegistrationController(new RegistroModel($this->connection), $this->renderer);
    }

    public function get($objectName) {
        return $this->objects[$objectName];
    }
}
