<?php
class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email']; // Ajouter l'email pour les notifications
                $_SESSION['success'] = "Connexion réussie. Bienvenue !";
                header('Location: ' . BASE_URL);
                exit;
            } else {
                $_SESSION['error'] = "Email ou mot de passe incorrect";
            }
        }
        require_once 'frontend/Views/auth/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $telephone = $_POST['telephone'] ?? '';

            // Validation
            if (empty($email) || empty($password) || empty($nom) || empty($prenom)) {
                $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis";
                require_once 'frontend/Views/auth/register.php';
                return;
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas";
                require_once 'frontend/Views/auth/register.php';
                return;
            }

            // Vérifier si l'email existe déjà
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Cet email est déjà utilisé";
                require_once 'frontend/Views/auth/register.php';
                return;
            }

            // Inscription
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (email, password, nom, prenom, telephone) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$email, $password_hash, $nom, $prenom, $telephone])) {
                $_SESSION['success'] = "Inscription réussie. Vous pouvez vous connecter.";
                header('Location: ' . BASE_URL . '/?page=login');
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription";
            }
        }
        require_once 'frontend/Views/auth/register.php';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL);
        exit;
    }
}
