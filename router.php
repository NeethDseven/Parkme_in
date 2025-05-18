<?php
$uri = $_SERVER['REQUEST_URI'];

// Rediriger les demandes de /Parking%20final/public/... vers le bon chemin
if (strpos($uri, '/Parking%20final/public/') === 0) {
    $correct_uri = str_replace('/Parking%20final/', '/Projet/Parking%20final/', $uri);
    header('Location: ' . $correct_uri);
    exit;
}

// Servir les fichiers statiques
if (preg_match('/\.(css|js|png|jpg|jpeg|gif)$/', $uri)) {
    $file_path = __DIR__ . $uri;
    if (file_exists($file_path)) {
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        if ($ext == 'css') header('Content-Type: text/css');
        elseif ($ext == 'js') header('Content-Type: application/javascript');
        readfile($file_path);
        exit;
    }
}

// Traiter la requête normalement
include __DIR__ . '/index.php';
