<?php
class Database {
    private static $conn = null;

    public static function connect() {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=localhost;dbname=parking_db",
                    "root",
                    "",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch(PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
            }
        }
        return self::$conn;
    }
}
