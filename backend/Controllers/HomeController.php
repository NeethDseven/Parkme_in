<?php
class HomeController {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function index() {
        // Récupérer les statistiques
        $stats = [
            'places_libres' => $this->getFreePlaces(),
            'places_totales' => $this->getTotalPlaces(),
            'places_par_type' => $this->getPlacesByType()
        ];
        
        require_once 'frontend/Views/home.php';
    }

    private function getFreePlaces() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM parking_spaces WHERE status = 'libre'");
        return $stmt->fetchColumn();
    }

    private function getTotalPlaces() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM parking_spaces");
        return $stmt->fetchColumn();
    }
    
    private function getPlacesByType() {
        $stmt = $this->db->query("
            SELECT type, 
                   COUNT(*) as total,
                   SUM(CASE WHEN status = 'libre' THEN 1 ELSE 0 END) as disponibles
            FROM parking_spaces
            GROUP BY type
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
