<?php
session_start();

// Définir les chemins de base
define('ROOT_PATH', __DIR__);
define('BASE_URL', '/Projet/Parking%20final');
define('PUBLIC_URL', BASE_URL . '/public');

// Connexion à la base de données
require_once 'config/database.php';

// Router simple
$page = $_GET['page'] ?? 'home';

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
            $action = $_GET['action'] ?? 'index';
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
        $action = $_GET['action'] ?? 'list';
        switch($action) {
            case 'list':
                $controller->listAvailable();
                break;
            case 'view':
                $controller->viewPlace();
                break;
        }
        break;
    case 'user':
        require_once 'app/Controllers/UserController.php';
        $controller = new UserController();
        $action = $_GET['action'] ?? 'reservations';
        switch($action) {
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
                $controller->showNotifications();
                break;
            case 'markNotificationRead':
                $controller->markNotificationRead();
                break;
        }
        break;
    default:
        require_once 'app/Views/404.php';
        break;
}
