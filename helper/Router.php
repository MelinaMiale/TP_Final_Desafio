<?php

class Router {
    private $configFactory;
    private $defaultController;
    private $defaultMethod;
    private $authorizationManager;

    public function __construct($configFactory, $defaultController,$defaultMethod, $authorizationManager) {
        $this->configFactory = $configFactory;
        $this->defaultController = $defaultController;
        $this->defaultMethod = $defaultMethod;
        $this->authorizationManager = $authorizationManager;
    }

    public function executeController($controllerParam, $methodParam) {

        $controllerParam = strtolower($controllerParam);

        if (in_array($controllerParam, ["login", "registration"])) {
            $controller = $this->getControllerFrom($controllerParam);
            $this->executeMethodFromController($controller, $methodParam);
            return;
        }

        if (!isset($_SESSION["user_name"])) {
            header("Location: /index.php?controller=login&method=loginForm");
            exit;
        }

        $role = $_SESSION["user_role"] ?? null;
        $normalizedControllerForAuth = $controllerParam
            ? $controllerParam . 'controller'
            : strtolower($this->defaultController);

        if (!$this->authorizationManager->isUserAuthorized($role, $normalizedControllerForAuth)) {
            header("Location: /index.php?controller=login&method=loginForm");
            exit;
        }

        $controller = $this->getControllerFrom($controllerParam);
        $this->executeMethodFromController($controller, $methodParam);
    }

    private function getControllerFrom($controllerName) {
        $controllerName = $this->getControllerName($controllerName);
        $controller = $this->configFactory->get($controllerName);

        if ($controller == null) {
            header("location: /index.php?controller=login&method=loginForm");
            exit;
        }

        return $controller;
    }

    private function executeMethodFromController($controller, $methodName) {
        call_user_func(
            array(
                $controller,
                $this->getMethodName($controller, $methodName)
            )
        );
    }

    public function getControllerName($controllerName) {
        return $controllerName ?
            ucfirst($controllerName) . 'Controller' :
            $this->defaultController;
    }

    public function getMethodName($controller, $methodName) {
        return method_exists($controller, $methodName) ? $methodName : $this->defaultMethod;
    }
}