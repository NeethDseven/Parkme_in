<?php

// Configuration des horaires d'ouverture
const HORAIRES_OUVERTURE = [
    'lundi'    => ['08:00', '20:00'],
    'mardi'    => ['08:00', '20:00'],
    'mercredi' => ['08:00', '20:00'],
    'jeudi'    => ['08:00', '20:00'],
    'vendredi' => ['08:00', '22:00'],
    'samedi'   => ['09:00', '22:00'],
    'dimanche' => ['09:00', '20:00']
];

// Configuration des notifications
const NOTIFICATION_DELAY = 2; // Heures avant la réservation
const EMAIL_FROM = 'parking@example.com';