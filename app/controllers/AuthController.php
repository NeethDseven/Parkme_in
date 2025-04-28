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
    
    public function register() {
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = isset($_POST['nom']) ? htmlspecialchars(trim($_POST['nom'])) : '';
            $prenom = isset($_POST['prenom']) ? htmlspecialchars(trim($_POST['prenom'])) : '';
            $email = isset($_POST['email']) ? trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            
            if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = "Tous les champs sont obligatoires.";
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Format d'email invalide.";
            } else if (strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères.";
            } else if ($password !== $confirm_password) {
                $error = "Les mots de passe ne correspondent pas.";
            } else {
                $existingUser = User::findByEmail($email);
                
                if ($existingUser) {
                    $error = "Cet email est déjà utilisé.";
                } else {
                    if (User::createUser($nom, $prenom, $email, $password)) {
                        $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                    } else {
                        $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                    }
                }
            }
        }
        
        $this->render('auth/register', ['error' => $error, 'success' => $success]);
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

    /*
    public function processLogin()
    {
        // Code de processLogin...
    }
    */
}
?>
