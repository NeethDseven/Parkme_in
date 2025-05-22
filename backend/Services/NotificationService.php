<?php
/**
 * Service de gestion des notifications
 */
class NotificationService {
    private $db;
    
    public function __construct() {
        $this->db = Database::connect();
    }
    
    /**
     * Crée une nouvelle notification
     */
    public function createNotification($userId, $titre, $message, $type = 'system') {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, titre, message, type)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $titre, $message, $type]);
    }
    
    /**
     * Crée une notification de paiement réussi
     */
    public function createPaymentNotification($userId, $reservationId, $amount) {
        $titre = "Paiement confirmé";
        $message = "Votre paiement de {$amount}€ pour la réservation #{$reservationId} a été confirmé.";
        return $this->createNotification($userId, $titre, $message, 'paiement');
    }
    
    /**
     * Récupère toutes les notifications d'un utilisateur
     */
    public function getUserNotifications($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère le nombre de notifications non lues d'un utilisateur
     */
    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE user_id = ? AND lu = 0
        ");
        $stmt->execute([$userId]);
        return intval($stmt->fetchColumn());
    }
    
    /**
     * Récupère les nouvelles notifications depuis une date donnée
     */
    public function getNewNotifications($userId, $since) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND created_at > ? AND lu = 0
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId, $since]);
        return $stmt->fetchAll();
    }
    
    /**
     * Marque une notification comme lue
     */
    public function markAsRead($notificationId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET lu = 1 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$notificationId, $userId]);
    }
    
    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead($userId) {
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET lu = 1 
            WHERE user_id = ? AND lu = 0
        ");
        return $stmt->execute([$userId]);
    }
    
    /**
     * Supprime les anciennes notifications
     * (Utile pour une tâche programmée de nettoyage)
     */
    public function cleanOldNotifications($daysToKeep = 30) {
        $stmt = $this->db->prepare("
            DELETE FROM notifications 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        return $stmt->execute([$daysToKeep]);
    }
}
