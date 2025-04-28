<?php

class BaseController {
    /**
     * Renders a view with the provided data
     * Automatically includes header and footer
     *
     * @param string $view The view path relative to app/views/
     * @param array $data Data to be extracted and passed to the view
     */
    protected function render($view, $data = []) {
        // Extract data to make variables available in the view
        if (!empty($data)) {
            extract($data);
        }
        
        // Define the base path for includes and assets
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(dirname(__FILE__)));
        }
        
        // Define a base URL for links
        if (!defined('BASE_URL')) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $script = dirname($_SERVER['SCRIPT_NAME']);
            define('BASE_URL', $protocol . $host . $script);
        }
        
        // Include header
        include BASE_PATH . '/app/views/includes/header.php';
        
        // Include the view file
        $viewPath = BASE_PATH . '/app/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            // Handle case when view doesn't exist
            echo "Error: View file not found: {$viewPath}";
        }
        
        // Include footer
        include BASE_PATH . '/app/views/includes/footer.php';
    }
    
    /**
     * Renders a view without header and footer
     *
     * @param string $view The view path relative to app/views/
     * @param array $data Data to be extracted and passed to the view
     */
    protected function renderPartial($view, $data = []) {
        // Extract data to make variables available in the view
        if (!empty($data)) {
            extract($data);
        }
        
        // Define the base path for includes and assets
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(dirname(__FILE__)));
        }
        
        // Include the view file
        $viewPath = BASE_PATH . '/app/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            // Handle case when view doesn't exist
            echo "Error: View file not found: {$viewPath}";
        }
    }
    
    /**
     * Rediriger vers une autre page
     *
     * @param string $controller Contrôleur
     * @param string $action Action
     * @return void
     */
    protected function redirect($controller, $action = 'index') {
        header("Location: index.php?controller=$controller&action=$action");
        exit();
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     *
     * @return bool
     */
    protected function isLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Vérifier si l'utilisateur est un administrateur
     *
     * @return bool
     */
    protected function isAdmin() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Déboguer le rôle dans la session
        error_log("Rôle utilisateur : " . ($_SESSION['user_role'] ?? 'non défini'));
        
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Protéger une page pour les utilisateurs connectés uniquement
     *
     * @return void
     */
    protected function requireLogin() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit();
        }
    }
    
    /**
     * Protéger une page pour les administrateurs uniquement
     *
     * @return void
     */
    protected function requireAdmin() {
        if (!$this->isAdmin()) {
            // Afficher un message d'erreur pour le débogage
            echo "Accès refusé : Vous n'êtes pas administrateur. ";
            echo "Role dans la session: " . ($_SESSION['user_role'] ?? 'non défini');
            echo "<br><a href='index.php?controller=dashboard'>Retour au tableau de bord</a>";
            exit();
        }
    }
}
?>
