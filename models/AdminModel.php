<?php
require_once __DIR__ . '/../enums/Role.php';
class AdminModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getAdminStats() {

        $sqlTotalUsers = "SELECT COUNT(*) as total FROM USUARIO";
        $totalUsers = $this->connection->query($sqlTotalUsers)[0]['total'];
        return [
            'total_users' => $totalUsers,
        ];
    }

}
