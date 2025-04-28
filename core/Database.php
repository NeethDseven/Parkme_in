<?php

class Database {
    private static $instance = null;
    private $connection;
    
    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
        $db_host = 'localhost';
        $db_name = 'app_gestion_parking'; // Nom de la base de données
        $db_user = 'root';
        $db_pass = '';

        try {
            $this->connection = new PDO(
                "mysql:host=$db_host;dbname=$db_name;charset=utf8",
                $db_user,
                $db_pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Initialize database tables if needed
            $this->initDatabase();
            
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }
    
    // Méthode pour obtenir l'instance unique (pattern Singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Méthode pour obtenir la connexion PDO
    public function getConnection() {
        return $this->connection;
    }
    
    // Méthodes d'aide pour les requêtes courantes
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    // Initialize database tables if they don't exist
    private function initDatabase() {
        try {
        } catch (PDOException $e) {
            die('Erreur d\'initialisation de la base de données : ' . $e->getMessage());
        }
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}