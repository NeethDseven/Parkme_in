<?php
// Configuration de l'application
return [
    // Configuration de la base de données
    'database' => [
        'host' => 'localhost',
        'name' => 'app_gestion_parking',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    
    // Configuration de l'application
    'app' => [
        'name' => 'ParkMeIn',
        'url' => 'http://localhost/projet/Parkme_in-master',
        'version' => '1.0.0',
        'debug' => true,
        'timezone' => 'Europe/Paris'
    ],
    
    // Configuration de l'envoi d'emails
    'email' => [
        'from' => 'noreply@parkme-in.com',
        'name' => 'ParkMeIn',
        'reply_to' => 'support@parkme-in.com'
    ],
    
    // Configuration des réservations
    'reservation' => [
        'min_duration' => 30, // Durée minimale en minutes
        'max_duration' => 720, // Durée maximale en minutes (12 heures)
        'advance_booking' => 30, // Jours maximum en avance pour réserver
        'cancellation_period' => 24, // Heures avant début pour pouvoir annuler
        'reminder_time' => 24, // Heures avant début pour envoyer un rappel
    ]
];
