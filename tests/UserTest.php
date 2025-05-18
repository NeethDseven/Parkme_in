<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Database;

class UserTest extends TestCase {
    private $db;
    private $testUser = [
        'email' => '', // Sera défini dynamiquement
        'password' => 'test123',
        'nom' => 'Test',
        'prenom' => 'User'
    ];

    protected function setUp(): void {
        parent::setUp();
        $this->db = Database::connect();
        // Générer un email unique pour chaque test
        $this->testUser['email'] = 'test_' . time() . rand(1000, 9999) . '@test.com';
        // Nettoyer les données de test existantes
        $this->db->query("DELETE FROM users WHERE email = '{$this->testUser['email']}'");
    }

    public function testUserCreation() {
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password, nom, prenom) 
            VALUES (?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $this->testUser['email'],
            password_hash($this->testUser['password'], PASSWORD_DEFAULT),
            $this->testUser['nom'],
            $this->testUser['prenom']
        ]);
        
        $this->assertTrue($result);
        
        // Vérifier que l'utilisateur existe
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$this->testUser['email']]);
        $user = $stmt->fetch();
        
        $this->assertNotFalse($user);
        $this->assertEquals($this->testUser['nom'], $user['nom']);
        $this->assertEquals($this->testUser['prenom'], $user['prenom']);
    }

    public function testUserAuthentication() {
        // Créer d'abord un utilisateur
        $hashedPassword = password_hash($this->testUser['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password, nom, prenom) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->testUser['email'],
            $hashedPassword,
            $this->testUser['nom'],
            $this->testUser['prenom']
        ]);

        // Tester l'authentification
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$this->testUser['email']]);
        $user = $stmt->fetch();

        $this->assertTrue(password_verify($this->testUser['password'], $user['password']));
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->db->query("DELETE FROM users WHERE email = '{$this->testUser['email']}'");
    }
}
