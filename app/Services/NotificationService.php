<?php
class NotificationService {
    private $db;
    private $logger;
    private $emailService;

    public function __construct() {
        // Utiliser la connexion de la classe Database
        require_once 'config/database.php';
        $this->db = Database::connect();
        
        // Initialiser les services associés si nécessaire
        if (file_exists('app/Services/LoggerService.php')) {
            require_once 'app/Services/LoggerService.php';
            $this->logger = new LoggerService();
        }
        
        if (file_exists('app/Services/EmailService.php')) {
            require_once 'app/Services/EmailService.php';
            $this->emailService = new EmailService();
        }
    }

    /**
     * Crée une notification de paiement
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $reservationId ID de la réservation
     * @param float $montant Montant du paiement
     * @param string $status Statut du paiement
     * @return bool Succès de l'opération
     */
    public function createPaymentNotification($userId, $reservationId, $montant, $status = 'validé') {
        $title = 'Paiement ' . $status;
        
        // Stocker les données structurées pour un meilleur affichage
        $messageData = json_encode([
            'montant' => $montant,
            'reservation_id' => $reservationId,
            'status' => $status
        ]);
        
        // Message formaté pour la rétrocompatibilité
        $message = "Votre paiement de {$montant}€ pour la réservation #{$reservationId} a été {$status}.";
        
        return $this->createNotification($userId, $title, $messageData, 'paiement');
    }
    
    /**
     * Crée une notification dans le système
     */
    public function createNotification($userId, $title, $message, $type = 'system', $isRead = 0) {
        if (!$this->db) {
            return false;
        }
        
        // Utiliser des requêtes préparées au lieu de real_escape_string()
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, titre, message, type, lu) 
                                   VALUES (:userId, :title, :message, :type, :isRead)");
        
        // Si le message est un tableau ou un objet, le convertir en JSON
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message);
        }
        
        $params = [
            ':userId' => (int)$userId,
            ':title' => $title,
            ':message' => $message,
            ':type' => $type,
            ':isRead' => (int)$isRead
        ];
        
        return $stmt->execute($params);
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
    
    /**
     * Récupère toutes les notifications d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Notifications de l'utilisateur
     */
    public function getUserNotifications($userId) {
        if (!$this->db) {
            return [];
        }
        
        $userId = (int)$userId;
        
        // Utiliser prepare/execute qui est plus sécurisé que query avec des variables intégrées
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        if (!$stmt) {
            return [];
        }
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marque une notification spécifique comme lue
     * 
     * @param int $notificationId ID de la notification
     * @param int $userId ID de l'utilisateur (pour vérification)
     * @return bool Succès de l'opération
     */
    public function markAsRead($notificationId, $userId) {
        if (!$this->db) {
            return false;
        }
        
        $notificationId = (int)$notificationId;
        $userId = (int)$userId;
        
        // Utiliser prepare/execute
        $stmt = $this->db->prepare("UPDATE notifications SET lu = 1 WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            return false;
        }
        
        return $stmt->execute([$notificationId, $userId]);
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool Succès de l'opération
     */
    public function markAllAsRead($userId) {
        if (!$this->db) {
            return false;
        }
        
        $userId = (int)$userId;
        
        // Utiliser prepare/execute
        $stmt = $this->db->prepare("UPDATE notifications SET lu = 1 WHERE user_id = ? AND lu = 0");
        if (!$stmt) {
            return false;
        }
        
        return $stmt->execute([$userId]);
    }

    public function getUnreadCount($userId) {
        if (!$this->db) {
            return 0;
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND lu = 0");
        if (!$stmt) {
            return 0;
        }
        
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
