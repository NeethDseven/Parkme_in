<?php
/**
 * Script de rappel pour les réservations à venir
 * À exécuter via un cron job, par exemple toutes les heures:
 * 0 * * * * php /path/to/send_reminders.php
 */

// Définition du répertoire de base
define('BASE_PATH', dirname(dirname(__FILE__)));

// Chargement des dépendances
require_once BASE_PATH . '/app/models/Database.php';
require_once BASE_PATH . '/app/models/Notification.php';
require_once BASE_PATH . '/app/models/Reservation.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/services/EmailService.php';

// Log de démarrage
error_log("Démarrage du script de rappel des réservations à " . date('Y-m-d H:i:s'));

// Connexion à la base de données
$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Récupérer les réservations qui commencent dans les prochaines 24 heures
    $stmt = $conn->prepare("
        SELECT r.*, u.nom, u.prenom, u.email 
        FROM reservations r
        JOIN utilisateurs u ON r.utilisateur_id = u.id
        WHERE r.statut = 'confirmée'
        AND r.date_debut BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
        AND NOT EXISTS (
            SELECT 1 FROM notifications n
            WHERE n.utilisateur_id = r.utilisateur_id
            AND n.type = 'rappel'
            AND n.message LIKE CONCAT('%', r.id, '%')
            AND DATE(n.date_creation) = CURRENT_DATE()
        )
    ");
    
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Nombre de réservations à rappeler: " . count($reservations));
    
    foreach ($reservations as $reservation) {
        // Créer une notification dans l'application
        $message = "Rappel: Votre réservation #" . $reservation['id'] . " commence le " . 
                  date('d/m/Y à H:i', strtotime($reservation['date_debut'])) . ". " . 
                  "Votre code d'accès est: " . $reservation['code_acces'];
        
        Notification::create($reservation['utilisateur_id'], $message, 'rappel');
        
        // Envoyer un email de rappel
        $res = new stdClass();
        foreach ($reservation as $key => $value) {
            $res->$key = $value;
        }
        
        EmailService::sendReservationReminder(
            $reservation['email'],
            $reservation['prenom'] . ' ' . $reservation['nom'],
            $res
        );
        
        error_log("Rappel envoyé pour la réservation #" . $reservation['id'] . 
                 " à l'utilisateur " . $reservation['prenom'] . ' ' . $reservation['nom']);
    }
    
    error_log("Fin du script de rappel des réservations à " . date('Y-m-d H:i:s'));
    
} catch (PDOException $e) {
    error_log("Erreur lors de l'exécution du script de rappel: " . $e->getMessage());
}
