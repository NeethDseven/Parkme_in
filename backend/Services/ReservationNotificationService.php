<?php
require_once 'backend/Services/EmailService.php';
require_once 'backend/Services/NotificationService.php';
require_once 'backend/Services/LoggerService.php';

/**
 * Service gérant les notifications liées aux réservations
 */
class ReservationNotificationService {
    private $db;
    private $emailService;
    private $notificationService;
    private $logger;
    
    public function __construct() {
        $this->db = Database::connect();
        $this->emailService = new EmailService();
        $this->notificationService = new NotificationService();
        $this->logger = new LoggerService();
    }
    
    /**
     * Vérifie les réservations qui débutent bientôt et envoie des notifications
     * 
     * @param int $minutesBeforeStart Nombre de minutes avant le début pour notifier
     * @return int Nombre de notifications envoyées
     */
    public function notifyUpcomingReservations($minutesBeforeStart = 30) {
        $sentCount = 0;
        
        try {
            // Trouver les réservations qui débutent dans X minutes
            $stmt = $this->db->prepare("
                SELECT r.*, p.numero as place_numero, p.type as place_type, 
                       u.email, u.prenom, u.nom, u.id as user_id
                FROM reservations r
                JOIN parking_spaces p ON r.place_id = p.id
                JOIN users u ON r.user_id = u.id
                WHERE r.status = 'confirmée'
                AND r.date_debut BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? MINUTE)
                AND r.notification_sent = 0
            ");
            
            $stmt->execute([$minutesBeforeStart]);
            $reservations = $stmt->fetchAll();
            
            $this->logger->info("Vérification des réservations débutant dans les $minutesBeforeStart prochaines minutes", [
                'count' => count($reservations)
            ]);
            
            foreach ($reservations as $reservation) {
                // Générer un code d'accès si nécessaire
                if (empty($reservation['code_acces'])) {
                    $codeAcces = $this->generateAccessCode();
                    
                    $updateStmt = $this->db->prepare("
                        UPDATE reservations 
                        SET code_acces = ? 
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$codeAcces, $reservation['id']]);
                    
                    $reservation['code_acces'] = $codeAcces;
                }
                
                // Créer une notification dans l'application
                $message = "Votre réservation de la place n°{$reservation['place_numero']} commence bientôt ! ";
                $message .= "Utilisez le code d'accès {$reservation['code_acces']} pour entrer dans le parking.";
                
                $this->notificationService->createNotification(
                    $reservation['user_id'],
                    'Votre réservation commence bientôt',
                    $message,
                    'reservation'
                );
                
                // Envoyer un email de notification
                $emailSubject = "Votre réservation commence bientôt - Parkme In";
                $emailBody = $this->buildReservationStartEmail($reservation);
                $this->emailService->sendEmail($reservation['email'], $emailSubject, $emailBody);
                
                // Marquer la notification comme envoyée
                $updateStmt = $this->db->prepare("
                    UPDATE reservations 
                    SET notification_sent = 1 
                    WHERE id = ?
                ");
                $updateStmt->execute([$reservation['id']]);
                
                $sentCount++;
                
                $this->logger->info("Notification de début de réservation envoyée", [
                    'user_id' => $reservation['user_id'],
                    'reservation_id' => $reservation['id'],
                    'email' => $reservation['email']
                ]);
            }
            
            return $sentCount;
            
        } catch (Exception $e) {
            $this->logger->error("Erreur lors de l'envoi des notifications de début de réservation: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Génère un code d'accès aléatoire pour le parking
     * 
     * @return string Code d'accès
     */
    private function generateAccessCode() {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    }
    
    /**
     * Construit le contenu HTML de l'email de début de réservation
     * 
     * @param array $reservation Données de la réservation
     * @return string Contenu HTML de l'email
     */
    private function buildReservationStartEmail($reservation) {
        $dateDebut = date('d/m/Y H:i', strtotime($reservation['date_debut']));
        $dateFin = date('d/m/Y H:i', strtotime($reservation['date_fin']));
        
        return "
            <html>
            <head>
                <title>Votre réservation commence bientôt</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; border: 1px solid #ddd; }
                    .code { font-size: 24px; font-weight: bold; color: #4CAF50; text-align: center; 
                            padding: 10px; border: 2px dashed #4CAF50; margin: 20px 0; }
                    .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Votre réservation commence bientôt</h1>
                    </div>
                    <div class='content'>
                        <p>Bonjour {$reservation['prenom']},</p>
                        <p>Votre réservation de la place n°{$reservation['place_numero']} commence bientôt !</p>
                        <p><strong>Détails de votre réservation :</strong></p>
                        <ul>
                            <li><strong>Place :</strong> N°{$reservation['place_numero']} (Type : {$reservation['place_type']})</li>
                            <li><strong>Début :</strong> $dateDebut</li>
                            <li><strong>Fin :</strong> $dateFin</li>
                        </ul>
                        <p>Voici votre code d'accès pour entrer dans le parking :</p>
                        <div class='code'>{$reservation['code_acces']}</div>
                        <p>Présentez ce code à l'entrée du parking ou utilisez l'application mobile pour scanner le QR code.</p>
                    </div>
                    <div class='footer'>
                        <p>© " . date('Y') . " Parkme In - Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}
