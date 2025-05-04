<?php
/**
 * Script pour mettre à jour le statut des réservations terminées
 * et libérer les places de parking correspondantes
 * À exécuter via un cron job, par exemple toutes les 15 minutes:
 * */15 * * * * php /path/to/update_reservations.php
 */

// Définition du répertoire de base
define('BASE_PATH', dirname(dirname(__FILE__)));

// Chargement des dépendances
require_once BASE_PATH . '/app/models/Database.php';
require_once BASE_PATH . '/app/models/Reservation.php';
require_once BASE_PATH . '/app/models/ParkingSpot.php';
require_once BASE_PATH . '/app/models/Notification.php';

// Log de démarrage
error_log("Démarrage du script de mise à jour des réservations à " . date('Y-m-d H:i:s'));

// Connexion à la base de données
$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Récupérer les réservations confirmées qui sont terminées
    $stmt = $conn->prepare("
        SELECT r.*
        FROM reservations r
        WHERE r.statut = 'confirmée'
        AND r.date_fin < NOW()
    ");
    
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Nombre de réservations à mettre à jour: " . count($reservations));
    
    foreach ($reservations as $reservation) {
        // Mettre à jour le statut de la réservation à "terminée"
        $updateStmt = $conn->prepare("UPDATE reservations SET statut = 'terminée' WHERE id = :id");
        $updateStmt->bindParam(':id', $reservation['id'], PDO::PARAM_INT);
        $updateStmt->execute();
        
        // Libérer la place de parking
        ParkingSpot::updateStatus($reservation['emplacement_id'], 'libre');
        
        // Créer une notification pour l'utilisateur
        $message = "Votre réservation #" . $reservation['id'] . " est maintenant terminée. Nous espérons que vous avez apprécié notre service.";
        Notification::create($reservation['utilisateur_id'], $message, 'info');
        
        error_log("Réservation #" . $reservation['id'] . " marquée comme terminée et place libérée.");
    }
    
    error_log("Fin du script de mise à jour des réservations à " . date('Y-m-d H:i:s'));
    
} catch (PDOException $e) {
    error_log("Erreur lors de l'exécution du script de mise à jour: " . $e->getMessage());
}
