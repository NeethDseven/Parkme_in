<?php
class NotificationService {
    private $db;
    private $logger;
    private $emailService;

    public function __construct() {
        $this->db = Database::connect();
        require_once 'app/Services/LoggerService.php';
        $this->logger = new LoggerService();
        require_once 'app/Services/EmailService.php';
        $this->emailService = new EmailService();
    }

    public function createNotification($userId, $titre, $message, $type = 'system') {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, titre, message, type)
            VALUES (?, ?, ?, ?)
        ");
        $result = $stmt->execute([$userId, $titre, $message, $type]);
        
        if ($result) {
            $this->logger->info("Notification créée", [
                'user_id' => $userId,
                'type' => $type
            ]);
        }
        
        return $result;
    }

    public function sendReservationReminders() {
        $count = 0;
        $stmt = $this->db->prepare("
            SELECT r.*, u.email, u.nom, u.prenom, ps.numero 
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            JOIN parking_spaces ps ON r.place_id = ps.id
            WHERE r.date_debut BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 HOUR)
            AND r.status = 'confirmée'
            AND r.notification_sent = 0
        ");
        $stmt->execute();
        
        foreach ($stmt->fetchAll() as $reservation) {
            // Créer notification interne
            $this->createNotification(
                $reservation['user_id'],
                'Rappel de réservation',
                "Votre réservation pour la place n°{$reservation['numero']} commence bientôt.",
                'rappel'
            );
            
            // Envoyer email
            $this->emailService->sendReservationReminder(
                $reservation['email'],
                $reservation['nom'],
                $reservation['numero'],
                $reservation['date_debut']
            );
            
            // Marquer comme envoyé
            $this->markNotificationSent($reservation['id']);
            $count++;
        }
        
        return $count; // Retourne le nombre de rappels envoyés
    }

    private function markNotificationSent($reservationId) {
        $stmt = $this->db->prepare("
            UPDATE reservations SET notification_sent = 1
            WHERE id = ?
        ");
        return $stmt->execute([$reservationId]);
    }
    
    public function getUserNotifications($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function markAsRead($notificationId) {
        $stmt = $this->db->prepare("
            UPDATE notifications SET lu = 1
            WHERE id = ?
        ");
        return $stmt->execute([$notificationId]);
    }

    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE user_id = ? AND lu = 0
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function generateSystemNotifications() {
        $count = 0;
        // Notifications pour places bientôt disponibles
        $stmt = $this->db->prepare("
            SELECT ps.id, ps.numero, r.date_fin, 
                   (SELECT GROUP_CONCAT(u.id) FROM users u WHERE u.notifications_active = 1) as user_ids
            FROM parking_spaces ps
            JOIN reservations r ON ps.id = r.place_id
            WHERE r.status = 'confirmée'
            AND r.date_fin BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute();
        
        foreach ($stmt->fetchAll() as $data) {
            if (!empty($data['user_ids'])) {
                $userIds = explode(',', $data['user_ids']);
                $message = "La place n°{$data['numero']} sera bientôt disponible (à " . 
                          date('H:i', strtotime($data['date_fin'])) . ").";
                          
                foreach ($userIds as $userId) {
                    $this->createNotification(
                        $userId,
                        'Place bientôt disponible',
                        $message,
                        'system'
                    );
                    $count++;
                }
            }
        }
        
        return $count; // Retourne le nombre de notifications système créées
    }
}
