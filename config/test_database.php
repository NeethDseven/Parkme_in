<?php
namespace App\Config;

class TestDatabase {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = 'localhost';
        $db   = 'parking_db';
        $user = 'root';
        $pass = '';

        try {
            $this->conn = new \PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch(\PDOException $e) {
            die("Erreur connexion : " . $e->getMessage());
        }
    }

    public static function connect() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }
}
