<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Définir le chemin de base de l'application
define('BASE_PATH', __DIR__);

// Charger la configuration
$config = require_once 'config/config.php';

// Appliquer la configuration
if (isset($config['app']['debug']) && !$config['app']['debug']) {
    error_reporting(0);
    ini_set('display_errors', 0);
}

if (isset($config['app']['timezone'])) {
    date_default_timezone_set($config['app']['timezone']);
}

// Fonction d'autoloading des classes
spl_autoload_register(function ($className) {
    $paths = [
        'core/',
        'app/controllers/',
        'app/models/',
        'app/services/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Démarrer la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log des requêtes pour débogage
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$postData = $_POST ? 'POST data present' : 'No POST data';
error_log("Request: $requestMethod $requestUri ($postData)");

// Inclure les fichiers de base essentiels
require_once 'core/Database.php';
require_once 'core/Router.php';

try {
    // Initialiser le router
    $router = new Router();

    // Traiter la requête
    $router->route();
} catch (Exception $e) {
    // Gérer les exceptions non capturées
    error_log("Erreur non capturée: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    if (file_exists('app/controllers/ErrorController.php')) {
        require_once 'app/controllers/ErrorController.php';
        $errorController = new ErrorController();
        $errorController->serverError();
    } else {
        // Affichage minimal d'erreur si ErrorController n'existe pas
        echo '<h1>Erreur</h1>';
        echo '<p>Une erreur est survenue. Veuillez réessayer ultérieurement.</p>';
        if (isset($config['app']['debug']) && $config['app']['debug']) {
            echo '<p>Message: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
}