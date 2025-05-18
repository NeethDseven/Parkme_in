<?php
// Script pour générer des notifications et des rappels
// À exécuter via un cron job

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Services/LoggerService.php';
require_once __DIR__ . '/../Services/NotificationService.php';
require_once __DIR__ . '/../Services/EmailService.php';

$logger = new LoggerService();
$logger->info("Démarrage de la génération de notifications");

$notificationService = new NotificationService();

try {
    // Envoyer les rappels de réservations
    $reminderCount = $notificationService->sendReservationReminders();
    
    // Générer les notifications système
    $systemCount = $notificationService->generateSystemNotifications();
    
    $logger->info("Notifications générées avec succès", [
        'reminders_sent' => $reminderCount,
        'system_notifications' => $systemCount
    ]);
} catch (Exception $e) {
    $logger->error("Erreur lors de la génération des notifications: " . $e->getMessage());
}
