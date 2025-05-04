<?php
// Define BASE_URL if it's not already defined
if (!defined('BASE_URL')) {
    define('BASE_URL', '/projet/Parkme_in-master/');
}

require_once 'core/BaseController.php';
require_once 'app/models/User.php';

class AuthController extends BaseController {
    public function login() {
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            
            if (empty($email) || empty($password)) {
                $error = "Tous les champs sont requis.";
            } else {
                require_once 'app/models/Database.php';
                $db = Database::getInstance(); 
                $conn = $db->getConnection();
                
                // Utiliser une requête plus simple
                $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Vérifier si le mot de passe correspond
                    if (password_verify($password, $user['password'])) {
                        // Démarrer une session si ce n'est pas déjà fait
                        if (session_status() == PHP_SESSION_NONE) {
                            session_start();
                        }
                        
                        // Stocker les informations utilisateur dans la session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_nom'] = $user['nom'];
                        $_SESSION['user_prenom'] = $user['prenom'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'] ?? 'user';
                        
                        // Redirection vers le tableau de bord
                        header("Location: index.php?controller=dashboard&action=index");
                        exit();
                    } else {
                        $error = "Identifiants incorrects. Veuillez réessayer.";
                    }
                } else {
                    $error = "Identifiants incorrects. Veuillez réessayer.";
                }
            }
        }
        
        include 'app/views/auth/login.php';
    }
    
    /**
     * Traitement du formulaire d'inscription
     */
    public function register() {
        // Vérifier si l'utilisateur est déjà connecté
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard', 'index');
            return;
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer et nettoyer les données du formulaire
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
            $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
            $terms = isset($_POST['terms']) ? true : false;
            
            // Validation des données
            if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "L'adresse email n'est pas valide.";
            } elseif (strlen($password) < 8) {
                $error = "Le mot de passe doit comporter au moins 8 caractères.";
            } elseif ($password !== $confirm_password) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif (!$terms) {
                $error = "Vous devez accepter les conditions générales.";
            } elseif (User::emailExists($email)) {
                $error = "Cette adresse email est déjà utilisée.";
            } else {
                // Hacher le mot de passe
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Créer le nouvel utilisateur
                $userId = User::create($nom, $prenom, $email, $password_hash, $telephone);
                
                if ($userId) {
                    // Créer une notification de bienvenue pour l'utilisateur
                    require_once 'app/models/Notification.php';
                    $message = "Bienvenue sur ParkMeIn, " . $prenom . " ! Votre compte a été créé avec succès.";
                    Notification::create($userId, $message, 'info');
                    
                    $success = "Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.";
                    
                    // Rediriger vers la page de connexion après 3 secondes
                    header("refresh:3;url=index.php?controller=auth&action=login");
                } else {
                    $error = "Une erreur s'est produite lors de la création de votre compte. Veuillez réessayer.";
                }
            }
        }
        
        $this->render('auth/register', [
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Handle user logout
     */
    public function logout()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to the home page instead of login
        header("Location: /projet/Parkme_in-master/");
        exit;
    }
}
?>
