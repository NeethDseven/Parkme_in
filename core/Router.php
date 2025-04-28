<?php

class Router {
    public function route() {
        // Add support for query parameter routing
        if (isset($_GET['controller']) && isset($_GET['action'])) {
            $controllerName = ucfirst($_GET['controller']) . 'Controller';
            $actionName = $_GET['action'];
            
            // Check if the controller file exists
            $controllerFile = "app/controllers/{$controllerName}.php";
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                
                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    
                    if (method_exists($controller, $actionName)) {
                        // Pass ID parameter if available
                        if (isset($_GET['id'])) {
                            $controller->$actionName($_GET['id']);
                        } else {
                            $controller->$actionName();
                        }
                        return;
                    }
                }
            }
            
            // If we reach here, the controller or action doesn't exist
            header("HTTP/1.0 404 Not Found");
            echo "404 Not Found: Controller or action doesn't exist.";
            exit;
        }
        
        // Continue with existing routing logic if query parameters don't match
        $controller = isset($_GET['controller']) ? $_GET['controller'] : 'home';
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';
        
        $controllerName = ucfirst($controller) . 'Controller';
        
        if (file_exists('app/controllers/' . $controllerName . '.php')) {
            require_once 'app/controllers/' . $controllerName . '.php';
            
            $controllerInstance = new $controllerName();
            
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                echo "Erreur : Action '$action' non trouvée dans le contrôleur '$controllerName'";
            }
        } else {
            echo "Erreur : Contrôleur '$controllerName' non trouvé";
        }
    }
}