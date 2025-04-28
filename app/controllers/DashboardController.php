<?php
require_once 'core/BaseController.php';

class DashboardController extends BaseController {
    public function index() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit();
        }
        
        $userData = [
            'id' => $_SESSION['user_id'],
            'nom' => $_SESSION['user_nom'] ?? '',
            'prenom' => $_SESSION['user_prenom'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user'
        ];
        
        // Débogage du rôle dans la dashboard
        error_log("Role affiché dans le dashboard: " . $userData['role']);
        
        include 'app/views/dashboard/index.php';
    }
    
    public function logout() {
        session_start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        $this->redirect('auth', 'login');
    }
}
?>
