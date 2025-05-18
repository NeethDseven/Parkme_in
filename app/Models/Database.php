<?php
namespace App\Models;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = 'localhost';
        $db   = 'parking_db';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        try {
            $this->conn = new PDO(
                "mysql:host=$host;dbname=$db;charset=$charset",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch(PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function connect() {
        return self::getInstance()->conn;
    }
}
