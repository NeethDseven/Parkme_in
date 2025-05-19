<?php
class BaseController {
    protected $db;
    protected $logger;
    
    public function __construct() {
        $this->db = Database::connect();
        require_once 'app/Services/LoggerService.php';
        $this->logger = new LoggerService();
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }
    
    protected function handleError($errno, $errstr, $errfile, $errline) {
        $this->logger->error("PHP Error: $errstr", [
            'file' => $errfile,
            'line' => $errline,
            'code' => $errno
        ]);
    }
    
    protected function handleException($exception) {
        $this->logger->error("Exception: " . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        $this->render('errors/500', ['message' => 'Une erreur est survenue']);
    }

    protected function render($view, $data = []) {
        // Extraire les données pour qu'elles soient disponibles comme variables
        extract($data);
        
        // Démarrer la mise en tampon de sortie
        ob_start();
        
        // Inclure la vue
        include_once ROOT_PATH . '/app/Views/' . $view . '.php';
        
        // Obtenir le contenu du tampon et le vider
        $pageContent = ob_get_clean();
        
        // Inclure le layout principal avec le contenu
        include_once ROOT_PATH . '/app/Views/layouts/main.php';
    }
    
    protected function redirect($url) {
        header('Location: ' . BASE_URL . '/' . $url);
        exit();
    }
    
    protected function setFlash($type, $message) {
        $_SESSION[$type] = $message;
    }
    
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
