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
        extract($data);
        require_once "app/Views/$view.php";
    }
}
