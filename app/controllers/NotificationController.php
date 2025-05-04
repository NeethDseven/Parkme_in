<?php
require_once 'core/BaseController.php';
require_once 'app/models/Notification.php';
require_once 'app/models/Database.php';

class NotificationController extends BaseController {
    
    /**
     * Affiche les notifications de l'utilisateur
     */
    public function index() {
        // Vérifier si l'utilisateur est connecté
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Récupérer les notifications de l'utilisateur
        $notifications = Notification::getAllByUserId($userId);
        
        // Marquer toutes les notifications comme lues
        foreach ($notifications as $notification) {
            if ($notification['lu'] == 0) {
                Notification::markAsRead($notification['id']);
            }
        }
        
        // Afficher la vue des notifications
        $this->render('notification/index', [
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Marque une notification comme lue
     */
    public function markAsRead() {
        // Vérifier si l'utilisateur est connecté
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $notificationId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if ($notificationId) {
            // Vérifier que la notification appartient bien à l'utilisateur
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT * FROM notifications WHERE id = :id AND utilisateur_id = :utilisateur_id");
            $stmt->bindParam(':id', $notificationId, PDO::PARAM_INT);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                Notification::markAsRead($notificationId);
            }
        }
        
        // Rediriger vers la page des notifications ou la page précédente
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php?controller=notification';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Supprime une notification
     */
    public function delete() {
        // Vérifier si l'utilisateur est connecté
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $notificationId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if ($notificationId) {
            // Vérifier que la notification appartient bien à l'utilisateur
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT * FROM notifications WHERE id = :id AND utilisateur_id = :utilisateur_id");
            $stmt->bindParam(':id', $notificationId, PDO::PARAM_INT);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                Notification::delete($notificationId);
            }
        }
        
        // Rediriger vers la page des notifications
        header('Location: index.php?controller=notification');
        exit;
    }
    
    /**
     * Supprime toutes les notifications de l'utilisateur
     */
    public function deleteAll() {
        // Vérifier si l'utilisateur est connecté
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Supprimer toutes les notifications de l'utilisateur
        Notification::deleteAllByUserId($userId);
        
        // Rediriger vers la page des notifications
        header('Location: index.php?controller=notification');
        exit;
    }
    
    /**
     * Récupère les notifications non lues (pour AJAX)
     */
    public function getUnread() {
        // Vérifier si l'utilisateur est connecté
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Non autorisé', 'count' => 0, 'notifications' => []]);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            // Récupérer les notifications non lues
            $notifications = Notification::getUnreadByUserId($userId);
            
            // Retourner les notifications au format JSON
            header('Content-Type: application/json');
            echo json_encode([
                'count' => count($notifications),
                'notifications' => $notifications
            ]);
            exit;
        } catch (Exception $e) {
            // Log d'erreur et réponse JSON d'erreur
            error_log("Erreur dans getUnread: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erreur serveur', 'count' => 0, 'notifications' => []]);
            exit;
        }
    }
}
?>
