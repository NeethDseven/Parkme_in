<?php
require_once 'core/BaseController.php';

class ErrorController extends BaseController {
    
    /**
     * Affiche une page d'erreur
     *
     * @param int $code Code HTTP de l'erreur
     * @param string $message Message d'erreur à afficher
     */
    public function show($code = 404, $message = 'Page introuvable') {
        http_response_code($code);
        
        $title = '';
        
        switch ($code) {
            case 404:
                $title = 'Page introuvable';
                break;
            case 403:
                $title = 'Accès refusé';
                break;
            case 500:
                $title = 'Erreur serveur';
                break;
            default:
                $title = 'Erreur';
        }
        
        $this->render('errors/error', [
            'code' => $code,
            'title' => $title,
            'message' => $message
        ]);
    }
    
    /**
     * Affiche une page d'erreur 404
     */
    public function notFound() {
        $this->show(404, 'La page que vous recherchez n\'existe pas.');
    }
    
    /**
     * Affiche une page d'erreur 403
     */
    public function forbidden() {
        $this->show(403, 'Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
    }
    
    /**
     * Affiche une page d'erreur serveur
     */
    public function serverError() {
        $this->show(500, 'Une erreur est survenue sur le serveur. Veuillez réessayer ultérieurement.');
    }
}
