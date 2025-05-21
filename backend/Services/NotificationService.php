<?php
class NotificationService {
    private $db;
    private $logger;
    private $emailService;

    public function __construct() {
        // Utiliser la connexion de la classe Database
        require_once 'backend/config/database.php';
        $this->db = Database::connect();
        
        // Initialiser les services associés si nécessaire
        if (file_exists('backend/Services/LoggerService.php')) {
            require_once 'backend/Services/LoggerService.php';
            $this->logger = new LoggerService();
        }
        
        if (file_exists('backend/Services/EmailService.php')) {
            require_once 'backend/Services/EmailService.php';
            $this->emailService = new EmailService();
        }
    }

    /**
     * Crée une notification pour un utilisateur
     */
    public function createNotification($user_id, $titre, $message, $type = 'system') {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, titre, message, type)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$user_id, $titre, $message, $type]);
    }

    /**
     * Crée une notification de paiement avec données structurées
     */
    public function createPaymentNotification($user_id, $reservation_id, $montant, $status = 'validé') {
        $data = json_encode([
            'reservation_id' => $reservation_id,
            'montant' => $montant,
            'status' => $status
        ]);
        
        return $this->createNotification(
            $user_id,
            'Confirmation de paiement',
            $data,
            'paiement'
        );
    }

    /**
     * Récupère les notifications d'un utilisateur
     */
    public function getUserNotifications($user_id, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead($notification_id, $user_id) {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET lu = TRUE
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$notification_id, $user_id]);
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead($user_id) {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET lu = TRUE
            WHERE user_id = ?
        ");
        return $stmt->execute([$user_id]);
    }

    /**
     * Compte les notifications non lues d'un utilisateur
     */
    public function getUnreadCount($user_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE user_id = ? AND lu = FALSE
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Envoie des rappels pour les réservations à venir
     */
    public function sendReservationReminders() {
        // Trouver les réservations qui commencent dans 24h et qui n'ont pas encore reçu de notification
        $stmt = $this->db->prepare("
            SELECT r.id, r.user_id, r.date_debut, u.email, u.nom, u.prenom, ps.numero
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            JOIN parking_spaces ps ON r.place_id = ps.id
            WHERE r.date_debut BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
            AND r.notification_sent = FALSE
            AND r.status = 'confirmée'
        ");
        $stmt->execute();
        $reservations = $stmt->fetchAll();
        
        foreach ($reservations as $reservation) {
            // Créer une notification dans l'application
            $this->createNotification(
                $reservation['user_id'],
                'Rappel de réservation',
                "Votre réservation de la place n°{$reservation['numero']} commence demain à " . 
                date('H:i', strtotime($reservation['date_debut'])) . ".",
                'rappel'
            );
            
            // Envoyer un email de rappel si le service est disponible
            if ($this->emailService) {
                $this->emailService->sendReservationReminder(
                    $reservation['email'],
                    $reservation['nom'] . ' ' . $reservation['prenom'],
                    $reservation['numero'],
                    $reservation['date_debut']
                );
            }
            
            // Marquer comme notification envoyée
            $updateStmt = $this->db->prepare("
                UPDATE reservations
                SET notification_sent = TRUE
                WHERE id = ?
            ");
            $updateStmt->execute([$reservation['id']]);
            
            // Logger l'action
            if ($this->logger) {
                $this->logger->info("Rappel de réservation envoyé", [
                    'reservation_id' => $reservation['id'],
                    'user_id' => $reservation['user_id']
                ]);
            }
        }
        
        return count($reservations);
    }
}
