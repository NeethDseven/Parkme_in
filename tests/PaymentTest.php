<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;

class PaymentTest extends TestCase {
    protected $db;

    protected function setUp(): void {
        parent::setUp();
        
        // Corriger les identifiants de connexion
        try {
            $this->db = new PDO(
                'mysql:host=localhost;dbname=parking_db;charset=utf8mb4',
                'root',  // Utilisateur MySQL par défaut pour XAMPP
                '',      // Mot de passe vide par défaut pour XAMPP
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch(\PDOException $e) {
            $this->markTestSkipped("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    public function testPaymentProcessing() {
        // Créer un utilisateur de test
        $this->db->exec("INSERT INTO users (email, password, nom) VALUES ('test_payment@example.com', 'password', 'Test')");
        $userId = $this->db->lastInsertId();
        
        // Créer une place de test
        $this->db->exec("INSERT INTO parking_spaces (numero, type, status) VALUES ('TEST-PAY', 'standard', 'libre')");
        $placeId = $this->db->lastInsertId();
        
        // Créer une réservation
        $stmt = $this->db->prepare("
            INSERT INTO reservations (user_id, place_id, date_debut, date_fin)
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR))
        ");
        $stmt->execute([$userId, $placeId]);
        $reservationId = $this->db->lastInsertId();
        
        // Test du paiement
        $montant = 25.00;
        $stmt = $this->db->prepare("
            INSERT INTO paiements (reservation_id, montant, status)
            VALUES (?, ?, 'en_attente')
        ");
        $result = $stmt->execute([$reservationId, $montant]);
        
        $this->assertTrue($result);
        $this->assertNotEquals(0, $this->db->lastInsertId());
        
        // Nettoyage
        $paymentId = $this->db->lastInsertId();
        $this->db->exec("DELETE FROM paiements WHERE id = $paymentId");
        $this->db->exec("DELETE FROM reservations WHERE id = $reservationId");
        $this->db->exec("DELETE FROM parking_spaces WHERE id = $placeId");
        $this->db->exec("DELETE FROM users WHERE id = $userId");
    }

    public function testPaymentValidation() {
        // Créer des données de test
        $this->db->exec("INSERT INTO users (email, password, nom) VALUES ('test_payment_val@example.com', 'password', 'Test')");
        $userId = $this->db->lastInsertId();
        
        $this->db->exec("INSERT INTO parking_spaces (numero, type, status) VALUES ('TEST-PAYVAL', 'standard', 'libre')");
        $placeId = $this->db->lastInsertId();
        
        $this->db->exec("
            INSERT INTO reservations (user_id, place_id, date_debut, date_fin)
            VALUES ($userId, $placeId, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR))
        ");
        $reservationId = $this->db->lastInsertId();
        
        $this->db->exec("
            INSERT INTO paiements (reservation_id, montant, status)
            VALUES ($reservationId, 25.00, 'en_attente')
        ");
        $paymentId = $this->db->lastInsertId();
        
        // Tester la validation du paiement
        $stmt = $this->db->prepare("
            UPDATE paiements SET status = 'valide' 
            WHERE id = ? AND status = 'en_attente'
        ");
        $result = $stmt->execute([$paymentId]);
        
        $this->assertTrue($result);
        
        // Nettoyage
        $this->db->exec("DELETE FROM paiements WHERE id = $paymentId");
        $this->db->exec("DELETE FROM reservations WHERE id = $reservationId");
        $this->db->exec("DELETE FROM parking_spaces WHERE id = $placeId");
        $this->db->exec("DELETE FROM users WHERE id = $userId");
    }

    protected function tearDown(): void {
        // Nettoyage des tests
        $this->db->query("DELETE FROM paiements WHERE reservation_id = 1");
    }
}
?>