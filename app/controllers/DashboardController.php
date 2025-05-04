<?php
require_once 'core/BaseController.php';
require_once 'app/models/Reservation.php';
require_once 'app/models/Payment.php';
require_once 'app/models/Notification.php';

class DashboardController extends BaseController {
    
    public function __construct() {
        // Vérifier si l'utilisateur est connecté
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }
    
    /**
     * Affiche le tableau de bord de l'utilisateur
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // Récupérer les réservations actives
        $activeReservations = $this->getActiveReservations($userId);
        
        // Récupérer les réservations à venir
        $upcomingReservations = $this->getUpcomingReservations($userId);
        
        // Récupérer le nombre total de réservations
        $totalReservations = $this->getTotalReservationsCount($userId);
        
        // Récupérer le montant total dépensé
        $totalSpent = $this->getTotalAmountSpent($userId);
        
        // Récupérer les notifications récentes
        $recentNotifications = Notification::getUnreadByUserId($userId);
        
        // Afficher la vue
        $this->render('dashboard/index', [
            'activeReservations' => $activeReservations,
            'upcomingReservations' => $upcomingReservations,
            'totalReservations' => $totalReservations,
            'totalSpent' => $totalSpent,
            'recentNotifications' => $recentNotifications
        ]);
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Détruire les variables de session
        $_SESSION = array();
        
        // Détruire la session
        session_destroy();
        
        // Rediriger vers la page d'accueil
        header('Location: index.php');
        exit;
    }
    
    /**
     * Récupère les réservations actives d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return array Liste des réservations actives
     */
    private function getActiveReservations($userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT r.*, p.numero as numero_place, pk.nom as parking_nom
                FROM reservations r
                JOIN places_parking p ON r.emplacement_id = p.id
                JOIN parkings pk ON p.parking_id = pk.id
                WHERE r.utilisateur_id = :utilisateur_id
                  AND r.statut = 'confirmée'
                  AND r.date_debut <= NOW()
                  AND r.date_fin > NOW()
                ORDER BY r.date_debut ASC
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getActiveReservations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère les réservations à venir d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return array Liste des réservations à venir
     */
    private function getUpcomingReservations($userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT r.*, p.numero as numero_place, pk.nom as parking_nom
                FROM reservations r
                JOIN places_parking p ON r.emplacement_id = p.id
                JOIN parkings pk ON p.parking_id = pk.id
                WHERE r.utilisateur_id = :utilisateur_id
                  AND r.statut = 'confirmée'
                  AND r.date_debut > NOW()
                ORDER BY r.date_debut ASC
                LIMIT 5
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getUpcomingReservations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère le nombre total de réservations d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return int Nombre total de réservations
     */
    private function getTotalReservationsCount($userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as total
                FROM reservations
                WHERE utilisateur_id = :utilisateur_id
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error in getTotalReservationsCount: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Récupère le montant total dépensé par un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return float Montant total dépensé
     */
    private function getTotalAmountSpent($userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT COALESCE(SUM(montant), 0) as total
                FROM paiements
                WHERE utilisateur_id = :utilisateur_id
                  AND statut = 'complete'
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error in getTotalAmountSpent: " . $e->getMessage());
            return 0;
        }
    }
}
?>
