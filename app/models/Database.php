<?php
namespace App\Models;

// Prevent multiple inclusion
if (!class_exists('App\\Models\\Database')) {
    class Database {
        private static $instance = null;
        private $host = 'localhost';
        private $username = 'root'; 
        private $password = ''; 
        private $database = 'app_gestion_parking';
        private $conn;
        
        private function __construct() {
            try {
                $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
                if ($this->conn->connect_error) {
                    throw new Exception("Erreur de connexion : " . $this->conn->connect_error);
                }
                $this->conn->set_charset("utf8");
            } catch (Exception $e) {
                $tmpConn = new mysqli($this->host, $this->username, $this->password);
                if (!$tmpConn->connect_error) {
                    if ($tmpConn->query("CREATE DATABASE IF NOT EXISTS " . $this->database)) {
                        $tmpConn->select_db($this->database);
                        if ($tmpConn->query($usersTable)) {
                            $this->conn = $tmpConn;
                        } else {
                            die("Erreur lors de la création de la table utilisateurs: " . $tmpConn->error);
                        }
                    } else {
                        die("Erreur lors de la création de la base de données: " . $tmpConn->error);
                    }
                } else {
                    die("Erreur de connexion au serveur MySQL : " . $tmpConn->connect_error);
                }
            }
        }
        
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        public function getConnection() {
            return $this->conn;
        }
    }
}
?>
