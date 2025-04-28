<?php

class ParkingController extends BaseController {
    
    public function index() {
        // Vérifier si l'utilisateur est connecté
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        // Inclure le modèle des parkings
        require_once 'app/models/Parking.php';
        
        // Récupérer la liste des parkings
        $parkings = Parking::getAll();
        
        // Rendre la vue avec le header et footer
        $this->render('parking/index', ['parkings' => $parkings]);
    }
    
    public function view() {
        // Vérifier si l'utilisateur est connecté
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        // Récupérer l'ID du parking
        $parkingId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$parkingId) {
            header('Location: index.php?controller=parking&action=index');
            exit;
        }
        
        // Inclure les modèles nécessaires
        require_once 'app/models/Parking.php';
        require_once __DIR__ . '/../models/ParkingSpot.php';
        
        // Récupérer les détails du parking
        $parking = Parking::getById($parkingId);
        
        // Récupérer les places disponibles dans ce parking
        $availableSpots = ParkingSpot::getAvailableByParkingId($parkingId);
        
        // Inclure la vue de détail du parking
        include 'app/views/parking/view.php';
    }
}
?>
