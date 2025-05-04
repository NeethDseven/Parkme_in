<?php
require_once 'core/BaseController.php';
require_once 'app/models/User.php';

class AdminController extends BaseController {
    public function __construct() {
        // Important: vérifier les sessions correctement
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Déboguer le rôle d'utilisateur
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            // Afficher un message d'erreur pour le débogage
            die("Accès refusé : Vous n'êtes pas administrateur. Role actuel: " . ($_SESSION['user_role'] ?? 'non défini'));
        }
    }
    
    /**
     * Affiche le tableau de bord administrateur
     */
    public function dashboard() {
        require_once 'app/models/Reservation.php';
        require_once 'app/models/ParkingSpot.php';
        
        // Récupérer les statistiques
        $stats = $this->getAdminStats();
        
        // Récupérer les réservations récentes
        $recentReservations = $this->getRecentReservations(5);
        
        // Récupérer les nouveaux utilisateurs
        $newUsers = $this->getNewUsers(5);
        
        $this->render('admin/dashboard', [
            'stats' => $stats,
            'recentReservations' => $recentReservations,
            'newUsers' => $newUsers
        ]);
    }
    
    /**
     * Récupère les statistiques pour le tableau de bord admin
     */
    private function getAdminStats() {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stats = [];
        
        // Nombre d'utilisateurs
        $stmt = $conn->query("SELECT COUNT(*) FROM utilisateurs");
        $stats['userCount'] = $stmt->fetchColumn();
        
        // Nombre total de réservations
        $stmt = $conn->query("SELECT COUNT(*) FROM reservations");
        $stats['reservationCount'] = $stmt->fetchColumn();
        
        // Nombre de réservations actives
        $stmt = $conn->query("SELECT COUNT(*) FROM reservations WHERE statut = 'confirmée' AND date_fin > NOW()");
        $stats['activeReservationCount'] = $stmt->fetchColumn();
        
        // Nombre de places de parking
        $stmt = $conn->query("SELECT COUNT(*) FROM places_parking");
        $stats['parkingSpotCount'] = $stmt->fetchColumn();
        
        // Revenus totaux
        $stmt = $conn->query("SELECT COALESCE(SUM(prix), 0) FROM reservations WHERE statut != 'annulée'");
        $stats['totalRevenue'] = $stmt->fetchColumn();
        
        // Revenus mensuels
        $stmt = $conn->query("SELECT COALESCE(SUM(prix), 0) FROM reservations WHERE statut != 'annulée' AND MONTH(date_debut) = MONTH(CURRENT_DATE()) AND YEAR(date_debut) = YEAR(CURRENT_DATE())");
        $stats['monthlyRevenue'] = $stmt->fetchColumn();
        
        // Revenus hebdomadaires
        $stmt = $conn->query("SELECT COALESCE(SUM(prix), 0) FROM reservations WHERE statut != 'annulée' AND date_debut >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)");
        $stats['weeklyRevenue'] = $stmt->fetchColumn();
        
        // Revenus quotidiens
        $stmt = $conn->query("SELECT COALESCE(SUM(prix), 0) FROM reservations WHERE statut != 'annulée' AND DATE(date_debut) = CURRENT_DATE()");
        $stats['dailyRevenue'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Récupère les réservations récentes
     */
    private function getRecentReservations($limit = 5) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT r.*, u.nom, u.prenom 
            FROM reservations r
            JOIN utilisateurs u ON r.utilisateur_id = u.id
            ORDER BY r.date_reservation DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les nouveaux utilisateurs
     */
    private function getNewUsers($limit = 5) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT * FROM utilisateurs
            ORDER BY date_creation DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function users() {
        $users = User::findAll();
        $this->render('admin/users', ['users' => $users]);
    }
    
    public function addUser() {
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = isset($_POST['nom']) ? htmlspecialchars(trim($_POST['nom'])) : '';
            $prenom = isset($_POST['prenom']) ? htmlspecialchars(trim($_POST['prenom'])) : '';
            $email = isset($_POST['email']) ? trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $role = isset($_POST['role']) ? $_POST['role'] : 'user';
            
            if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                $error = "Tous les champs sont obligatoires.";
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Format d'email invalide.";
            } else if (strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères.";
            } else {
                $existingUser = User::findByEmail($email);
                
                if ($existingUser) {
                    $error = "Cet email est déjà utilisé.";
                } else {
                    if (User::createUser($nom, $prenom, $email, $password, $role)) {
                        $success = "Utilisateur ajouté avec succès.";
                    } else {
                        $error = "Erreur lors de l'ajout de l'utilisateur.";
                    }
                }
            }
        }
        
        $this->render('admin/add_user', ['error' => $error, 'success' => $success]);
    }
    
    public function editUser() {
        $error = null;
        $success = null;
        
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $this->redirect('admin', 'users');
        }
        
        $userId = (int)$_GET['id'];
        $user = User::findById($userId);
        
        if (!$user) {
            $this->redirect('admin', 'users');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => isset($_POST['nom']) ? htmlspecialchars(trim($_POST['nom'])) : $user->nom,
                'prenom' => isset($_POST['prenom']) ? htmlspecialchars(trim($_POST['prenom'])) : $user->prenom,
                'email' => isset($_POST['email']) ? trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)) : $user->email,
                'role' => isset($_POST['role']) ? $_POST['role'] : $user->role,
            ];
            
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = "Format d'email invalide.";
            } else {
                $existingUser = User::findByEmail($data['email']);
                if ($existingUser && $existingUser->id != $userId) {
                    $error = "Cet email est déjà utilisé par un autre utilisateur.";
                } else {
                    if (User::updateUser($userId, $data)) {
                        $success = "Utilisateur mis à jour avec succès.";
                        $user = User::findById($userId); // Recharger les données
                    } else {
                        $error = "Erreur lors de la mise à jour de l'utilisateur.";
                    }
                }
            }
        }
        
        $this->render('admin/edit_user', [
            'user' => $user,
            'error' => $error,
            'success' => $success
        ]);
    }
    
    public function deleteUser() {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $userId = (int)$_GET['id'];
            
            // Ne pas supprimer l'utilisateur connecté ou un autre admin
            $user = User::findById($userId);
            if ($userId != $_SESSION['user_id'] && (!$user || $user->role != 'admin')) {
                User::delete($userId);
            }
        }
        
        $this->redirect('admin', 'users');
    }
    
    public function parkingSpots() {
        require_once 'app/models/ParkingSpot.php';
        $spots = ParkingSpot::findAll();
        $this->render('admin/parking_spots', ['spots' => $spots]);
    }
    
    public function addParkingSpot() {
        require_once 'app/models/ParkingSpot.php';
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numero = trim($_POST['numero']);
            $type = $_POST['type'] ?? 'normale';
            $statut = $_POST['statut'] ?? 'libre';

            if (empty($numero)) {
                $error = "Le numéro de place est obligatoire.";
            } else {
                if (ParkingSpot::create($numero, $type, $statut)) {
                    $success = "Place ajoutée avec succès.";
                } else {
                    $error = "Erreur lors de l'ajout.";
                }
            }
        }

        $spots = ParkingSpot::findAll();
        $this->render('admin/parking_spots', [
            'spots' => $spots,
            'error' => $error,
            'success' => $success
        ]);
    }
    
    public function deleteParkingSpot() {
        require_once 'app/models/ParkingSpot.php';

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            ParkingSpot::delete((int)$_GET['id']);
        }

        $this->redirect('admin', 'parkingSpots');
    }

    public function reservations() {
        require_once 'app/models/Reservation.php';
        $reservations = Reservation::findAll();
        $this->render('admin/reservations', ['reservations' => $reservations]);
    }

    public function deleteReservation() {
        require_once 'app/models/Reservation.php';

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            Reservation::delete((int)$_GET['id']);
        }

        $this->redirect('admin', 'reservations');
    }
}

?>
