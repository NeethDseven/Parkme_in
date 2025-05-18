<?php
namespace Tests;

use PDO;

class ReservationTest extends TestCase {
    protected $db;

    protected function setUp(): void {
        $this->db = new PDO('mysql:host=localhost;dbname=parking_db', 'root', '');
    }

    /** @test */
    public function testCreateReservation() {
        // Utiliser un email unique avec timestamp pour éviter les conflits
        $uniqueEmail = 'test_' . time() . '@example.com';
        
        // Créer d'abord un utilisateur et une place pour le test
        $this->db->exec("INSERT INTO users (email, password, nom) VALUES ('$uniqueEmail', 'password', 'Test')");
        
        // Vérifier si la place existe déjà, sinon la créer
        $stmt = $this->db->query("SELECT id FROM parking_spaces WHERE numero = 'TEST-A1'");
        if (!$stmt->fetch()) {
            $this->db->exec("INSERT INTO parking_spaces (numero, type, status) VALUES ('TEST-A1', 'standard', 'libre')");
        }
        
        // Récupérer les IDs créés
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$uniqueEmail]);
        $userId = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT id FROM parking_spaces WHERE numero = 'TEST-A1'");
        $placeId = $stmt->fetchColumn();
        
        // Création d'une réservation test
        $stmt = $this->db->prepare("
            INSERT INTO reservations (user_id, place_id, date_debut, date_fin)
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR))
        ");
        $result = $stmt->execute([$userId, $placeId]);
        $this->assertTrue($result);
        
        // Nettoyer après le test
        $this->db->exec("DELETE FROM reservations WHERE user_id = $userId");
        $this->db->exec("DELETE FROM users WHERE email = '$uniqueEmail'");
    }

    /** @test */
    public function testReservationOverlap() {
        // Créer un utilisateur de test
        $uniqueEmail = 'test_overlap_' . time() . '@example.com';
        $this->db->exec("INSERT INTO users (email, password, nom) VALUES ('$uniqueEmail', 'password', 'Test')");
        $userId = $this->db->lastInsertId();
        
        // Créer une place de test
        $this->db->exec("INSERT INTO parking_spaces (numero, type, status) VALUES ('TEST-OVP', 'standard', 'libre')");
        $placeId = $this->db->lastInsertId();
        
        // Créer une première réservation
        $dateDebut = date('Y-m-d H:i:s');
        $dateFin = date('Y-m-d H:i:s', strtotime('+2 hours'));
        
        $stmt = $this->db->prepare("
            INSERT INTO reservations (user_id, place_id, date_debut, date_fin, status)
            VALUES (?, ?, ?, ?, 'confirmee')
        ");
        $stmt->execute([$userId, $placeId, $dateDebut, $dateFin]);
        
        // Vérifier si une réservation sur le même créneau est possible
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE place_id = ? 
            AND status != 'annulee'
            AND (
                (date_debut BETWEEN ? AND ?) 
                OR (date_fin BETWEEN ? AND ?)
            )
        ");
        $stmt->execute([$placeId, $dateDebut, $dateFin, $dateDebut, $dateFin]);
        $count = $stmt->fetchColumn();
        
        // Il devrait y avoir au moins une réservation qui se chevauche (celle qu'on vient de créer)
        $this->assertEquals(1, $count, "La place devrait avoir une réservation");
        
        // Nettoyer après le test
        $this->db->exec("DELETE FROM reservations WHERE user_id = $userId");
        $this->db->exec("DELETE FROM users WHERE id = $userId");
        $this->db->exec("DELETE FROM parking_spaces WHERE id = $placeId");
    }
}
