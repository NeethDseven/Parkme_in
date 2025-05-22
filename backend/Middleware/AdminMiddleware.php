<?php
/**
 * Middleware pour vérifier si l'utilisateur est un administrateur
 */
class AdminMiddleware {
    /**
     * Vérifie si l'utilisateur est un administrateur
     * 
     * @return bool True si l'utilisateur est un administrateur, false sinon
     */
    public function check() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page";
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }
        
        // Vérifier si l'utilisateur est un administrateur
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Vous n'avez pas les droits nécessaires pour accéder à cette page";
            header('Location: ' . BASE_URL);
            exit;
        }
        
        return true;
    }
}
