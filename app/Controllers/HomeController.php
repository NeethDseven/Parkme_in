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
            'places_totales' => $this->getTotalPlaces()
        ];
        
        require_once 'app/Views/home.php';
    }

    private function getFreePlaces() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM parking_spaces WHERE status = 'libre'");
        return $stmt->fetchColumn();
    }

    private function getTotalPlaces() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM parking_spaces");
        return $stmt->fetchColumn();
    }
}
