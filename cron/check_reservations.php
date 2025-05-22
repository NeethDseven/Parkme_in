<?php
/**
 * Script cron qui vérifie les réservations débutant prochainement
 * et envoie des notifications aux utilisateurs
 * 
 * À exécuter toutes les 15 minutes via cron ou tâche planifiée
 */

// Définir le chemin racine
define('ROOT_PATH', dirname(__DIR__));

// Charger les dépendances
require_once ROOT_PATH . '/backend/config/database.php';
require_once ROOT_PATH . '/backend/Services/ReservationNotificationService.php';
require_once ROOT_PATH . '/backend/Services/LoggerService.php';

// Définir BASE_URL pour les liens dans les emails
define('BASE_URL', '/Projet/Parkme_In');

$logger = new LoggerService();
$logger->info("Exécution du script de vérification des réservations commençant bientôt");

try {
    // Initialiser le service de notification
    $notificationService = new ReservationNotificationService();
    
    // Envoyer des notifications pour les réservations débutant dans les 30 prochaines minutes
    $notificationCount = $notificationService->notifyUpcomingReservations(30);
    
    $logger->info("Notifications de début de réservation envoyées", [
        'count' => $notificationCount
    ]);
    
    echo "Succès : $notificationCount notification(s) envoyée(s).\n";
    
} catch (Exception $e) {
    $logger->error("Erreur lors de l'exécution du script de vérification des réservations : " . $e->getMessage());
    echo "Erreur : " . $e->getMessage() . "\n";
}
