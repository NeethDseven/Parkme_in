<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Database;

class ParkingSpaceTest extends TestCase {
    private $db;

    protected function setUp(): void {
        parent::setUp();
        require_once __DIR__ . '/../vendor/autoload.php';
        $this->db = Database::connect();
    }

    public function testCreateParkingSpace() {
        // Nettoyer d'abord
        $this->db->query("DELETE FROM parking_spaces WHERE numero = 'TEST-01'");
        
        $stmt = $this->db->prepare("
            INSERT INTO parking_spaces (numero, type, status)
            VALUES ('TEST-01', 'standard', 'libre')
        ");
        $result = $stmt->execute();
        
        $this->assertTrue($result);
        
        // Vérifier la création
        $stmt = $this->db->prepare("SELECT * FROM parking_spaces WHERE numero = 'TEST-01'");
        $stmt->execute();
        $place = $stmt->fetch();
        
        $this->assertNotFalse($place);
        $this->assertEquals('standard', $place['type']);
        $this->assertEquals('libre', $place['status']);
    }

    public function testUpdateParkingStatus() {
        // Créer d'abord la place
        $this->testCreateParkingSpace();
        
        // Mise à jour du statut
        $stmt = $this->db->prepare("
            UPDATE parking_spaces 
            SET status = 'occupe'
            WHERE numero = 'TEST-01'
        ");
        $stmt->execute();
        
        // Vérification du statut
        $stmt = $this->db->prepare("
            SELECT status FROM parking_spaces WHERE numero = 'TEST-01'
        ");
        $stmt->execute();
        $status = $stmt->fetchColumn();
        
        $this->assertEquals('occupe', $status);
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->db->query("DELETE FROM parking_spaces WHERE numero = 'TEST-01'");
    }
}
