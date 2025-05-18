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
                header('Location: ' . BASE_URL);
                exit;
            }
        }
        require_once 'app/Views/auth/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';

            $stmt = $this->db->prepare("INSERT INTO users (email, password, nom, prenom) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$email, $password, $nom, $prenom])) {
                header('Location: ' . BASE_URL . '/?page=login');
                exit;
            }
        }
        require_once 'app/Views/auth/register.php';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL);
        exit;
    }
}
