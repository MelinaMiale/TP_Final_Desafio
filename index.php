<?php
session_start();
include("helper/ConfigFactory.php");

$configFactory = new ConfigFactory();
$router = $configFactory->get("router");

$controller = $_GET["controller"] ?? null;
$method = $_GET["method"] ?? null;

$router->executeController($controller, $method);
