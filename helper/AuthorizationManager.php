<?php

class AuthorizationManager {
    private $permissions;

    public function __construct($permissions) {
        $this->permissions = $permissions;
    }

    public function isUserAuthorized($role, $controllerName) {
        $controller = strtolower($controllerName);
        if (!$role) {
            $role = 'guest';
        }

        if (!isset($this->permissions[$role])) {
            return false;
        }

        $allowedControllers = array_map('strtolower', $this->permissions[$role]);
        return in_array($controller, $allowedControllers, true);
    }
}
