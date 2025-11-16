<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
include_once(__DIR__ . '/models/PlayableQuestion.php');
session_start();
include("helper/ConfigFactory.php");

$configFactory = new ConfigFactory();
$router = $configFactory->get("router");

$controller = $_GET["controller"] ?? null;
$method = $_GET["method"] ?? null;

$router->executeController($controller, $method);
