<?php
// Fichier pour servir les ressources statiques

// Détermine le type de contenu en fonction de l'extension du fichier
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$ext = pathinfo($path, PATHINFO_EXTENSION);

switch ($ext) {
    case 'css':
        header('Content-Type: text/css');
        break;
    case 'js':
        header('Content-Type: application/javascript');
        break;
    case 'jpg':
    case 'jpeg':
        header('Content-Type: image/jpeg');
        break;
    case 'png':
        header('Content-Type: image/png');
        break;
    default:
        header('Content-Type: text/plain');
}

// Récupérer le chemin du fichier à servir
$filePath = __DIR__ . str_replace('/Projet/Parking final/public', '', $path);

// Vérifier si le fichier existe et le servir
if (file_exists($filePath)) {
    readfile($filePath);
} else {
    header('HTTP/1.0 404 Not Found');
    echo 'File not found';
}
