<?php
session_start();

// Définir les chemins de base
define('ROOT_PATH', __DIR__);
define('BASE_URL', '/Projet/Parking%20final');
define('PUBLIC_URL', BASE_URL . '/public');

// Déterminer si la requête concerne un fichier statique
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$ext = pathinfo($path, PATHINFO_EXTENSION);

// Liste des extensions de fichiers statiques
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'pdf', 'woff', 'woff2', 'ttf', 'eot'];

// Si c'est un fichier statique, servir le fichier
if (in_array($ext, $staticExtensions)) {
    // Définir le type de contenu en fonction de l'extension
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
        case 'gif':
            header('Content-Type: image/gif');
            break;
        case 'svg':
            header('Content-Type: image/svg+xml');
            break;
        case 'ico':
            header('Content-Type: image/x-icon');
            break;
        case 'pdf':
            header('Content-Type: application/pdf');
            break;
        case 'woff':
            header('Content-Type: font/woff');
            break;
        case 'woff2':
            header('Content-Type: font/woff2');
            break;
        case 'ttf':
            header('Content-Type: font/ttf');
            break;
        case 'eot':
            header('Content-Type: application/vnd.ms-fontobject');
            break;
        default:
            header('Content-Type: text/plain');
    }
    
    // Récupérer le chemin du fichier à servir
    // Correction de la logique d'identification du chemin du fichier
    $urlPath = urldecode($path);  // Décoder l'URL pour gérer les espaces et caractères spéciaux
    
    // Déterminer le chemin physique du fichier
    if (strpos($urlPath, '/Projet/Parking final/public/') === 0) {
        // Si le fichier est explicitement dans le dossier public
        $filePath = ROOT_PATH . str_replace('/Projet/Parking final', '', $urlPath);
    } else {
        // Si l'URL ne contient pas explicitement "/public/", on vérifie d'abord dans public
        $publicFilePath = ROOT_PATH . '/public' . str_replace('/Projet/Parking final', '', $urlPath);
        
        if (file_exists($publicFilePath)) {
            $filePath = $publicFilePath;
        } else {
            // Sinon on cherche à la racine
            $filePath = ROOT_PATH . str_replace('/Projet/Parking final', '', $urlPath);
        }
    }
    
    // Vérifier si le fichier existe et le servir
    if (file_exists($filePath)) {
        readfile($filePath);
        exit;
    } else {
        header('HTTP/1.0 404 Not Found');
        echo "File not found: $filePath (requested: $urlPath)";
        exit;
    }
}

// Connexion à la base de données pour les routes d'application
require_once 'config/database.php';

// Router simple
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// Routage basique
switch($page) {
    case 'home':
        require_once 'app/Controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
        
    case 'login':
        require_once 'app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;
        
    case 'register':
        require_once 'app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->register();
        break;
        
    case 'logout':
        require_once 'app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
        
    case 'admin':
        require_once 'app/Middleware/AdminMiddleware.php';
        require_once 'app/Controllers/AdminController.php';
        $middleware = new AdminMiddleware();
        if ($middleware->check()) {
            $controller = new AdminController();
            
            switch($action) {
                case 'users':
                    $controller->listUsers();
                    break;
                case 'addUser':
                    $controller->addUser();
                    break;
                case 'editUser':
                    $controller->editUser();
                    break;
                case 'deleteUser':
                    $controller->deleteUser();
                    break;
                case 'places':
                    $controller->listPlaces();
                    break;
                case 'addPlace':
                    $controller->addPlace();
                    break;
                case 'editPlace':
                    $controller->editPlace();
                    break;
                case 'deletePlace':
                    $controller->deletePlace();
                    break;
                case 'reservations':
                    $controller->listReservations();
                    break;
                case 'addReservation':
                    $controller->addReservation();
                    break;
                case 'editReservation':
                    $controller->editReservation();
                    break;
                case 'deleteReservation':
                    $controller->deleteReservation();
                    break;
                case 'refunds':
                    $controller->manageRefunds();
                    break;
                case 'processRefund':
                    $controller->processRefund();
                    break;
                default:
                    $controller->index();
            }
        }
        break;
        
    case 'parking':
        require_once 'app/Controllers/ParkingController.php';
        $controller = new ParkingController();
        
        switch($action) {
            case 'list':
                $controller->listAvailable();
                break;
            case 'view':
                $controller->viewPlace();
                break;
            default:
                $controller->listAvailable();
        }
        break;
        
    case 'user':
        require_once 'app/Controllers/UserController.php';
        $controller = new UserController();
        
        switch($action) {
            case 'dashboard':
                $controller->dashboard();
                break;
            case 'reservations':
                $controller->listReservations();
                break;
            case 'cancelReservation':
                $controller->cancelReservation();
                break;
            case 'payment':
                $controller->showPayment();
                break;
            case 'processPayment':
                $controller->processPayment();
                break;
            case 'history':
                $controller->paymentHistory();
                break;
            case 'refund':
                $controller->requestRefund();
                break;
            case 'downloadReceipt':
                $controller->downloadReceipt();
                break;
            case 'notifications':
                $controller->notifications();
                break;
            case 'mark_read':
                $controller->mark_read();
                break;
            case 'mark_all_read':
                $controller->mark_all_read();
                break;
            case 'profile':
                $controller->profile();
                break;
            default:
                $controller->dashboard();
        }
        break;
        
    default:
        require_once 'app/Views/404.php';
}
