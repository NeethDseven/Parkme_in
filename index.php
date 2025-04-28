<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers de base
require_once 'core/Database.php';
require_once 'core/Router.php';
require_once 'core/BaseController.php';
require_once 'core/BaseModel.php';
require_once 'app/models/Database.php';

// Initialize the router
$router = new Router();

// Handle the request - this will now support both URL formats
$router->route();