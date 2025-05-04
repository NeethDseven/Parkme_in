<?php
require_once 'core/BaseController.php';
require_once 'app/models/Parking.php';

class HomeController extends BaseController {
    
    /**
     * Affiche la page d'accueil
     */
    public function index() {
        // Récupérer tous les parkings disponibles
        $parkings = Parking::getAll();
        
        // Afficher la vue de la page d'accueil
        $this->render('home/index', [
            'parkings' => $parkings
        ]);
    }
    
    /**
     * Affiche la page "À propos"
     */
    public function about() {
        $this->render('home/about');
    }
    
    /**
     * Affiche la page de contact
     */
    public function contact() {
        $success = false;
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
            $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
            $subject = isset($_POST['subject']) ? htmlspecialchars(trim($_POST['subject'])) : '';
            $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
            
            // Validation basique
            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                $error = "Tous les champs sont obligatoires.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "L'adresse email n'est pas valide.";
            } else {
                // Envoyer l'email (simulé pour le développement)
                $success = true;
                
                // Log du message pour le développement
                error_log("Message de contact reçu de $name ($email): $subject");
            }
        }
        
        $this->render('home/contact', [
            'success' => $success,
            'error' => $error
        ]);
    }
    
    /**
     * Affiche les termes et conditions
     */
    public function terms() {
        $this->render('home/terms');
    }
    
    /**
     * Affiche la politique de confidentialité
     */
    public function privacy() {
        $this->render('home/privacy');
    }
}
?>
