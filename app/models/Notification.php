<?php
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../app/models/Database.php';

class Notification extends Model {
    // Propriétés
    public $id;
    public $utilisateur_id;
    public $message;
    public $type;
    public $lu;
    public $date_creation;
    
    protected static $table = 'notifications';
    
    /**
     * Créer une nouvelle notification
     *
     * @param int $userId ID de l'utilisateur
     * @param string $message Message de notification
     * @param string $type Type de notification (info, success, warning, danger)
     * @return int|bool ID de la notification ou false en cas d'échec
     */
    public static function create($userId, $message, $type = 'info') {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO notifications (utilisateur_id, message, type, lu, date_creation)
                VALUES (:utilisateur_id, :message, :type, 0, NOW())
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':type', $type);
            
            if ($stmt->execute()) {
                return $conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marquer une notification comme lue
     *
     * @param int $id ID de la notification
     * @return bool Succès ou échec
     */
    public static function markAsRead($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("UPDATE notifications SET lu = 1 WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors du marquage de la notification comme lue: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les notifications non lues d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return array Tableau des notifications
     */
    public static function getUnreadByUserId($userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT * FROM notifications 
                WHERE utilisateur_id = :utilisateur_id AND lu = 0
                ORDER BY date_creation DESC
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer toutes les notifications d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param int $limit Nombre maximum de notifications à récupérer
     * @return array Tableau des notifications
     */
    public static function getAllByUserId($userId, $limit = 20) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT * FROM notifications 
                WHERE utilisateur_id = :utilisateur_id
                ORDER BY date_creation DESC
                LIMIT :limit
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Supprimer une notification
     *
     * @param int $id ID de la notification
     * @return bool Succès ou échec
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("DELETE FROM notifications WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de la notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer toutes les notifications d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return bool Succès ou échec
     */
    public static function deleteAllByUserId($userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("DELETE FROM notifications WHERE utilisateur_id = :utilisateur_id");
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression des notifications: " . $e->getMessage());
            return false;
        }
    }
}
?>
