<?php

class Router {
    private $defaultController = 'Home';
    private $defaultAction = 'index';
    
    public function route() {
        // Récupérer le contrôleur et l'action depuis l'URL
        $controller = isset($_GET['controller']) ? $_GET['controller'] : $this->defaultController;
        $action = isset($_GET['action']) ? $_GET['action'] : $this->defaultAction;
        
        // Formater le nom de la classe du contrôleur
        $controllerName = ucfirst(strtolower($controller)) . 'Controller';
        $controllerFile = 'app/controllers/' . $controllerName . '.php';
        
        // Vérifier si le fichier du contrôleur existe
        if (file_exists($controllerFile)) {
            // Inclure le fichier du contrôleur
            require_once $controllerFile;
            
            // Vérifier si la classe du contrôleur existe
            if (class_exists($controllerName)) {
                // Créer une instance du contrôleur
                $controllerInstance = new $controllerName();
                
                // Vérifier si l'action existe
                if (method_exists($controllerInstance, $action)) {
                    // Appeler l'action
                    $controllerInstance->$action();
                } else {
                    // Action non trouvée
                    $this->handleError('Action introuvable', 404);
                }
            } else {
                // Classe du contrôleur non trouvée
                $this->handleError('Contrôleur introuvable', 404);
            }
        } else {
            // Fichier du contrôleur non trouvé
            $this->handleError('Page introuvable', 404);
        }
    }
    
    private function handleError($message, $code = 500) {
        http_response_code($code);
        
        // Vérifier si le contrôleur d'erreur existe
        $errorControllerFile = 'app/controllers/ErrorController.php';
        
        if (file_exists($errorControllerFile)) {
            require_once $errorControllerFile;
            
            if (class_exists('ErrorController')) {
                $errorController = new ErrorController();
                
                if (method_exists($errorController, 'show')) {
                    $errorController->show($code, $message);
                    return;
                }
            }
        }
        
        // Si le contrôleur d'erreur n'est pas disponible, afficher un message d'erreur simple
        echo '<h1>Erreur ' . $code . '</h1>';
        echo '<p>' . $message . '</p>';
    }
}