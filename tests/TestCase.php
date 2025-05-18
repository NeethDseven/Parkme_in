<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase {
    protected $db;

    protected function setUp(): void {
        parent::setUp();
        
        // Connexion directe à la base de données pour les tests
        try {
            $this->db = new PDO(
                'mysql:host=localhost;dbname=parking_db;charset=utf8mb4',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch(\PDOException $e) {
            $this->markTestSkipped("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
}
