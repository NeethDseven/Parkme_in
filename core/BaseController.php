<?php
class BaseController {
    /**
     * Charge et affiche une vue
     *
     * @param string $view Chemin de la vue à charger
     * @param array $data Variables à passer à la vue
     */
    protected function render($view, $data = []) {
        // Extraire les données pour les rendre accessibles dans la vue
        extract($data);
        
        // Construire le chemin complet vers la vue
        $viewFile = 'app/views/' . $view . '.php';
        
        // Vérifier si le fichier de vue existe
        if (file_exists($viewFile)) {
            // Inclure la vue
            require_once $viewFile;
        } else {
            // Afficher une erreur si la vue n'existe pas
            throw new Exception("Vue non trouvée: {$viewFile}");
        }
    }
    
    /**
     * Redirige vers une autre action/contrôleur
     *
     * @param string $controller Nom du contrôleur
     * @param string $action Nom de l'action
     * @param array $params Paramètres supplémentaires
     */
    protected function redirect($controller, $action = 'index', $params = []) {
        $url = 'index.php?controller=' . $controller . '&action=' . $action;
        
        // Ajouter les paramètres supplémentaires s'il y en a
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $url .= '&' . $key . '=' . urlencode($value);
            }
        }
        
        // Rediriger
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     *
     * @return bool True si l'utilisateur est connecté, false sinon
     */
    protected function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Vérifie si l'utilisateur est administrateur
     *
     * @return bool True si l'utilisateur est administrateur, false sinon
     */
    protected function isAdmin() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Envoi une réponse JSON
     *
     * @param mixed $data Données à envoyer au format JSON
     * @param int $statusCode Code HTTP (par défaut 200)
     */
    protected function sendJson($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Valide les champs d'un formulaire
     *
     * @param array $rules Règles de validation
     * @param array $data Données à valider
     * @return array Tableau des erreurs (vide si aucune erreur)
     */
    protected function validate($rules, $data) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? $data[$field] : null;
            
            // Règle required
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "Le champ {$field} est requis.";
                continue;
            }
            
            // Si le champ est vide et n'est pas requis, on passe à la règle suivante
            if (empty($value)) {
                continue;
            }
            
            // Règle email
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Le champ {$field} doit être une adresse email valide.";
            }
            
            // Règle min
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = (int) $matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = "Le champ {$field} doit contenir au moins {$min} caractères.";
                }
            }
            
            // Règle numeric
            if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
                $errors[$field] = "Le champ {$field} doit être un nombre.";
            }
            
            // Règle date
            if (strpos($rule, 'date') !== false) {
                $date = date_create($value);
                if (!$date) {
                    $errors[$field] = "Le champ {$field} doit être une date valide.";
                }
            }
        }
        
        return $errors;
    }
}
?>
