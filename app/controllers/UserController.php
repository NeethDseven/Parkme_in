<?php
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
    
    public function profile() {
        // Vérifier si l'utilisateur est connecté
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        // Récupérer l'ID de l'utilisateur connecté
        $userId = $_SESSION['user_id'];
        
        // Récupérer les informations de l'utilisateur
        $user = User::findById($userId);
        
        // Inclure la vue du profil utilisateur
        include 'app/views/user/profile.php';
    }

    /**
     * Edit user profile
     */
    public function edit() {
        // Vérifier si l'utilisateur est connecté
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        // Récupérer l'ID de l'utilisateur connecté
        $userId = $_SESSION['user_id'];
        
        // Récupérer les informations de l'utilisateur
        $user = User::findById($userId);
        
        if (!$user) {
            header('Location: index.php?controller=user&action=profile');
            exit;
        }
        
        $errors = [];
        $success = false;
        
        // Traitement du formulaire d'édition
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
            $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
            
            // Validation des données
            if (empty($nom)) {
                $errors[] = "Le nom est requis.";
            }
            
            if (empty($prenom)) {
                $errors[] = "Le prénom est requis.";
            }
            
            if (empty($email)) {
                $errors[] = "L'email est requis.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide.";
            }
            
            // Si l'email a changé, vérifier qu'il n'est pas déjà utilisé
            if ($email !== $user['email']) {
                $existingUser = User::findByEmail($email);
                if ($existingUser && $existingUser['id'] != $userId) {
                    $errors[] = "Cet email est déjà utilisé par un autre compte.";
                }
            }
            
            // Mise à jour du profil si pas d'erreurs
            if (empty($errors)) {
                $userData = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'telephone' => $telephone
                ];
                
                // Fix: Pass both the user ID and the data to update
                if (User::update($userId, $userData)) {
                    $success = true;
                    $user = User::findById($userId); // Refresh user data
                } else {
                    $errors[] = "Erreur lors de la mise à jour du profil.";
                }
            }
        }
        
        // Afficher le formulaire d'édition avec les données actuelles
        $this->render('user/edit', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success
        ]);
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