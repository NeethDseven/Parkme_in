<?php
session_start();

// Définir les chemins de base
define('ROOT_PATH', __DIR__);
define('BASE_URL', '/Projet/Parking%20final');
define('PUBLIC_URL', BASE_URL . '/public');

// ----------------------
// GESTION DES FICHIERS STATIQUES
// ----------------------

// Fonction pour obtenir l'extension d'un fichier
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Vérifier si c'est une demande pour un fichier statique
$uri = $_SERVER['REQUEST_URI'];

// Rediriger les demandes mal formées
if (strpos($uri, '/Parking%20final/public/') === 0) {
    $correct_uri = str_replace('/Parking%20final/', '/Projet/Parking%20final/', $uri);
    header('Location: ' . $correct_uri);
    exit;
}

// Servir les fichiers statiques
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
$fileExtension = getFileExtension($uri);

if (in_array($fileExtension, $staticExtensions)) {
    // Définir les types MIME
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject'
    ];
    
    // Construire le chemin du fichier
    if (strpos($uri, '/public/') !== false) {
        $filePath = ROOT_PATH . substr($uri, strpos($uri, '/public/'));
    } else {
        $filePath = ROOT_PATH . $uri;
    }
    
    // Vérifier si le fichier existe et le servir
    if (file_exists($filePath)) {
        header("Content-Type: " . ($mimeTypes[$fileExtension] ?? 'application/octet-stream'));
        readfile($filePath);
        exit;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "Fichier non trouvé: " . htmlspecialchars($filePath);
        exit;
    }
}

// ----------------------
// ROUTAGE DE L'APPLICATION
// ----------------------

// Connexion à la base de données
require_once 'config/database.php';

// Récupérer les paramètres de routage
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

try {
    // Routage principal de l'application
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
} catch (Exception $e) {
    // Journaliser l'exception
    error_log("Exception non gérée: " . $e->getMessage());
    
    // Afficher une page d'erreur
    $_SESSION['error'] = "Une erreur est survenue. Veuillez réessayer plus tard.";
    require_once 'app/Views/error.php';
}
