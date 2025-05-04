<?php
require_once 'core/BaseController.php';
require_once 'app/models/User.php';

class UserController extends BaseController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $user = User::findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user'] = $user;
                header('Location: index.php?controller=dashboard&action=index');
            } else {
                echo "Email ou mot de passe incorrect.";
            }
        } else {
            include 'app/views/auth/login.php';
        }
    }

    /**
     * Register a new user
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'telephone' => $_POST['telephone'] ?? '',
                // Default role is 'user', set in the model
            ];
            
            // Validate form data
            $errors = [];
            
            if (empty($userData['nom'])) {
                $errors[] = "Le nom est requis.";
            }
            
            if (empty($userData['email'])) {
                $errors[] = "L'email est requis.";
            } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide.";
            }
            
            if (empty($userData['password'])) {
                $errors[] = "Le mot de passe est requis.";
            } elseif (strlen($userData['password']) < 6) {
                $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
            }
            
            // If validation passes, try to create the user
            if (empty($errors)) {
                $userId = User::create($userData);
                
                if ($userId) {
                    // Success, redirect to login
                    header('Location: /login?success=1');
                    exit;
                } else {
                    // Generic error message
                    $errors[] = "Erreur lors de l'ajout de l'utilisateur. Peut-être que l'email existe déjà.";
                }
            }
            
            // If we get here, there were errors
            // Re-render the registration form with errors
            $this->render('users/register', [
                'errors' => $errors,
                'userData' => $userData
            ]);
        } else {
            // Display the registration form
            $this->render('users/register');
        }
    }
    
    /**
     * Affiche le profil de l'utilisateur connecté
     */
    public function profile() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Récupérer les informations de l'utilisateur
        $user = User::findById($userId);
        
        if (!$user) {
            $this->redirect('dashboard', 'index');
            return;
        }
        
        $this->render('user/profile', ['user' => $user]);
    }

    /**
     * Modifie les informations du profil utilisateur
     */
    public function edit() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $user = User::findById($userId);
        
        if (!$user) {
            $this->redirect('dashboard', 'index');
            return;
        }
        
        // Traiter le formulaire si soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = isset($_POST['nom']) ? htmlspecialchars(trim($_POST['nom'])) : $user['nom'];
            $prenom = isset($_POST['prenom']) ? htmlspecialchars(trim($_POST['prenom'])) : $user['prenom'];
            $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : $user['email'];
            $telephone = isset($_POST['telephone']) ? htmlspecialchars(trim($_POST['telephone'])) : $user['telephone'];
            
            // Validation de l'email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->render('user/edit', [
                    'user' => $user,
                    'error' => "L'adresse email n'est pas valide."
                ]);
                return;
            }
            
            // Vérifier que l'email n'est pas déjà utilisé par un autre utilisateur
            if ($email !== $user['email'] && User::emailExists($email, $userId)) {
                $this->render('user/edit', [
                    'user' => $user,
                    'error' => "Cette adresse email est déjà utilisée."
                ]);
                return;
            }
            
            // Mettre à jour le profil
            $data = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephone
            ];
            
            if (User::update($userId, $data)) {
                // Mettre à jour les informations de session
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_prenom'] = $prenom;
                $_SESSION['user_email'] = $email;
                
                $this->redirect('user', 'profile', ['success' => 'Profil mis à jour avec succès']);
            } else {
                $this->render('user/edit', [
                    'user' => $user,
                    'error' => "Une erreur est survenue lors de la mise à jour du profil."
                ]);
            }
        } else {
            $this->render('user/edit', ['user' => $user]);
        }
    }

    /**
     * Change user password
     */
    public function changePassword() {
        // Vérifier si l'utilisateur est connecté
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        // Récupérer l'ID de l'utilisateur connecté
        $userId = $_SESSION['user_id'];
        
        $errors = [];
        $success = false;
        
        // Traitement du formulaire de changement de mot de passe
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
            $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
            $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            
            // Validation des données
            if (empty($currentPassword)) {
                $errors[] = "Le mot de passe actuel est requis.";
            }
            
            if (empty($newPassword)) {
                $errors[] = "Le nouveau mot de passe est requis.";
            } elseif (strlen($newPassword) < 6) {
                $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }
            
            // Vérifier le mot de passe actuel
            if (empty($errors)) {
                $user = User::findById($userId);
                
                if (!$user || !password_verify($currentPassword, $user['password'])) {
                    $errors[] = "Le mot de passe actuel est incorrect.";
                } else {
                    // Mettre à jour le mot de passe
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    if (User::updatePassword($userId, $hashedPassword)) {
                        $success = true;
                    } else {
                        $errors[] = "Erreur lors de la mise à jour du mot de passe.";
                    }
                }
            }
        }
        
        // Afficher le formulaire de changement de mot de passe
        $this->render('user/change_password', [
            'errors' => $errors,
            'success' => $success
        ]);
    }
}
?>