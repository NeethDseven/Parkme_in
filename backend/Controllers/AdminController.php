<?php
class AdminController {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function index() {
        $stats = [
            'places_libres' => $this->getFreePlaces(),
            'places_totales' => $this->getTotalPlaces(),
            'reservations_jour' => $this->getTodayReservations(),
            'revenus_mois' => $this->getMonthlyRevenue(),
            'occupation_semaine' => $this->getWeeklyOccupation(),
            'revenus_mois_detail' => $this->getDetailedMonthlyRevenue()
        ];
        
        // Préparer les données pour JavaScript dans un format unifié
        $jsData = [
            'hasCharts' => true,
            'occupationData' => $stats['occupation_semaine'],
            'revenueData' => $stats['revenus_mois_detail']
        ];
        
        // Ajouter les scripts JS nécessaires
        $extraJS = ['dashboard.js'];
        
        // Vérifier manuellement si des notifications de début de réservation doivent être envoyées
        if (isset($_GET['check_notifications']) && $_GET['check_notifications'] == 1) {
            require_once 'backend/Services/ReservationNotificationService.php';
            $notificationService = new ReservationNotificationService();
            $count = $notificationService->notifyUpcomingReservations(30);
            
            if ($count > 0) {
                $_SESSION['success'] = "$count notification(s) de début de réservation envoyée(s).";
            } else {
                $_SESSION['info'] = "Aucune notification de début de réservation à envoyer.";
            }
        }
        
        require_once 'frontend/Views/admin/dashboard.php';
    }

    /**
     * Gestion des statistiques avancées en temps réel
     */
    public function getRealTimeStats() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['error' => 'Non autorisé']);
            exit;
        }
        
        $period = $_GET['period'] ?? 'day';
        $stats = [];
        
        switch($period) {
            case 'week':
                $stats['occupation'] = $this->getWeeklyOccupation();
                $stats['revenue'] = $this->getWeeklyRevenue();
                break;
            case 'month':
                $stats['occupation'] = $this->getMonthlyOccupation();
                $stats['revenue'] = $this->getDetailedMonthlyRevenue();
                break;
            case 'year':
                $stats['occupation'] = $this->getYearlyOccupation();
                $stats['revenue'] = $this->getYearlyRevenue();
                break;
            default: // day
                $stats['occupation'] = $this->getTodayOccupation();
                $stats['revenue'] = $this->getTodayRevenue();
        }
        
        $stats['places_libres'] = $this->getFreePlaces();
        $stats['places_totales'] = $this->getTotalPlaces();
        $stats['taux_occupation'] = ($stats['places_totales'] > 0) 
            ? round(($stats['places_totales'] - $stats['places_libres']) / $stats['places_totales'] * 100) 
            : 0;
        
        echo json_encode($stats);
        exit;
    }

    // Nouvelles méthodes pour les statistiques avancées
    private function getTodayOccupation() {
        $stmt = $this->db->query("
            SELECT 
                HOUR(date_debut) as heure,
                COUNT(*) as nombre 
            FROM reservations 
            WHERE DATE(date_debut) = CURDATE() 
            AND status = 'confirmée'
            GROUP BY HOUR(date_debut)
            ORDER BY heure
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTodayRevenue() {
        $stmt = $this->db->query("
            SELECT 
                HOUR(date_paiement) as heure,
                SUM(montant) as total 
            FROM paiements 
            WHERE DATE(date_paiement) = CURDATE()
            AND status = 'valide'
            GROUP BY HOUR(date_paiement)
            ORDER BY heure
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getWeeklyRevenue() {
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(date_paiement, '%Y-%m-%d') as jour,
                SUM(montant) as total 
            FROM paiements 
            WHERE date_paiement >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
            AND status = 'valide'
            GROUP BY jour
            ORDER BY jour
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getMonthlyOccupation() {
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(date_debut, '%Y-%m-%d') as jour,
                COUNT(*) as nombre 
            FROM reservations 
            WHERE date_debut >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            AND status = 'confirmée'
            GROUP BY jour
            ORDER BY jour
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getYearlyOccupation() {
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(date_debut, '%Y-%m') as mois,
                COUNT(*) as nombre 
            FROM reservations 
            WHERE date_debut >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR)
            AND status = 'confirmée'
            GROUP BY mois
            ORDER BY mois
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getYearlyRevenue() {
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(date_paiement, '%Y-%m') as mois,
                SUM(montant) as total 
            FROM paiements 
            WHERE date_paiement >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR)
            AND status = 'valide'
            GROUP BY mois
            ORDER BY mois
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getFreePlaces() {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM parking_spaces 
            WHERE status = 'libre'
        ");
        return $stmt->fetchColumn();
    }

    private function getTotalPlaces() {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM parking_spaces
        ");
        return $stmt->fetchColumn();
    }

    private function getTodayReservations() {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM reservations 
            WHERE DATE(date_debut) = CURDATE()
            AND status = 'confirmée'
        ");
        return $stmt->fetchColumn();
    }

    private function getMonthlyRevenue() {
        $stmt = $this->db->query("
            SELECT SUM(p.montant) 
            FROM paiements p
            JOIN reservations r ON p.reservation_id = r.id
            WHERE MONTH(p.date_paiement) = MONTH(CURRENT_DATE())
            AND YEAR(p.date_paiement) = YEAR(CURRENT_DATE())
            AND p.status = 'valide'
            AND r.status = 'confirmée'
            AND NOT EXISTS (
                SELECT 1 FROM remboursements rem 
                WHERE rem.paiement_id = p.id 
                AND rem.status IN ('en_cours', 'effectué')
            )
        ");
        return $stmt->fetchColumn() ?? 0;
    }

    private function getWeeklyOccupation() {
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(date_debut, '%W') as jour,
                COUNT(*) as places_occupees
            FROM reservations
            WHERE date_debut BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()
            GROUP BY jour
            ORDER BY FIELD(jour, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getDetailedMonthlyRevenue() {
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(date_paiement, '%d/%m') as jour,
                SUM(montant) as montant
            FROM paiements
            WHERE date_paiement >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            AND status = 'valide'
            GROUP BY jour
            ORDER BY date_paiement
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listUsers() {
        require_once 'backend/Services/PaginationService.php';
        
        $currentPage = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT) ?: 1;
        $searchTerm = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
        $itemsPerPage = 10; // 10 utilisateurs par page
        
        // Requête de base pour compter le nombre total d'utilisateurs
        $countSql = "SELECT COUNT(*) FROM users";
        $params = [];
        
        // Requête pour récupérer les utilisateurs
        $sql = "SELECT * FROM users";
        
        // Ajouter la recherche si un terme est fourni
        if ($searchTerm) {
            $searchParam = '%' . $searchTerm . '%';
            $countSql .= " WHERE email LIKE ? OR nom LIKE ? OR prenom LIKE ?";
            $sql .= " WHERE email LIKE ? OR nom LIKE ? OR prenom LIKE ?";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        // Exécuter la requête de comptage
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $totalUsers = $stmt->fetchColumn();
        
        // Initialiser la pagination
        $pagination = new PaginationService($totalUsers, $itemsPerPage, $currentPage);
        
        // Compléter la requête avec tri et pagination
        $sql .= " ORDER BY created_at DESC LIMIT ?, ?";
        $params[] = $pagination->getOffset();
        $params[] = $pagination->getLimit();
        
        // Exécuter la requête paginée
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        // Préparer les paramètres pour les liens de pagination
        $queryParams = [];
        if ($searchTerm) {
            $queryParams['search'] = $searchTerm;
        }
        
        // Générer les liens de pagination
        $paginationLinks = $pagination->createLinks(BASE_URL, array_merge(['page' => 'admin', 'action' => 'users'], $queryParams));
        
        require_once 'frontend/Views/admin/users/list.php';
    }

    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $role = $_POST['role'] ?? 'user';
            
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password, nom, prenom, role) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$email, $password, $nom, $prenom, $role])) {
                $_SESSION['success'] = "Utilisateur ajouté avec succès";
                header('Location: ' . BASE_URL . '/?page=admin&action=users');
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'utilisateur";
            }
        }
        
        require_once 'frontend/Views/admin/users/add.php';
    }

    public function editUser() {
        $id = $_GET['id'] ?? null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $role = $_POST['role'] ?? 'user';
            
            $sql = "UPDATE users SET email = ?, nom = ?, prenom = ?, role = ? WHERE id = ?";
            $params = [$email, $nom, $prenom, $role, $id];
            
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET email = ?, nom = ?, prenom = ?, role = ?, password = ? WHERE id = ?";
                $params = [$email, $nom, $prenom, $role, $password, $id];
            }
            
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute($params)) {
                $_SESSION['success'] = "Utilisateur mis à jour avec succès";
                header('Location: ' . BASE_URL . '/?page=admin&action=users');
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur";
            }
        }
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        require_once 'frontend/Views/admin/users/edit.php';
    }

    public function deleteUser() {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$id) {
            $_SESSION['error'] = "ID d'utilisateur invalide";
            header('Location: ' . BASE_URL . '/?page=admin&action=users');
            exit;
        }
        
        // Empêcher la suppression de son propre compte
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte";
            header('Location: ' . BASE_URL . '/?page=admin&action=users');
            exit;
        }
        
        // Vérifier que l'utilisateur existe
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé";
            header('Location: ' . BASE_URL . '/?page=admin&action=users');
            exit;
        }
        
        // Option: vérifier s'il s'agit du dernier admin (pour éviter de supprimer tous les admins)
        if ($user['role'] === 'admin') {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $adminCount = $stmt->fetchColumn();
            
            if ($adminCount <= 1) {
                $_SESSION['error'] = "Impossible de supprimer le dernier compte administrateur";
                header('Location: ' . BASE_URL . '/?page=admin&action=users');
                exit;
            }
        }
        
        // Vérifier s'il y a des réservations associées
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ?");
        $stmt->execute([$id]);
        $reservationCount = $stmt->fetchColumn();
        
        if ($reservationCount > 0) {
            try {
                $this->db->beginTransaction();
                
                // Supprimer d'abord les données associées dans les tables liées
                $this->deleteUserData($id);
                
                // Puis supprimer l'utilisateur
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
                if (!$stmt->execute([$id])) {
                    throw new Exception("Erreur lors de la suppression de l'utilisateur");
                }
                
                $this->db->commit();
                $_SESSION['success'] = "Utilisateur et toutes ses données associées supprimés avec succès";
            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error'] = $e->getMessage();
            }
        } else {
            // Pas de réservations, on peut simplement supprimer l'utilisateur
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['success'] = "Utilisateur supprimé avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur";
            }
        }
        
        header('Location: ' . BASE_URL . '/?page=admin&action=users');
        exit;
    }

    /**
     * Supprime toutes les données associées à un utilisateur
     */
    private function deleteUserData($userId) {
        // Supprimer les notifications
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Récupérer les IDs des réservations
        $stmt = $this->db->prepare("SELECT id FROM reservations WHERE user_id = ?");
        $stmt->execute([$userId]);
        $reservationIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($reservationIds as $reservationId) {
            // Supprimer les paiements et données associées
            $stmt = $this->db->prepare("SELECT id FROM paiements WHERE reservation_id = ?");
            $stmt->execute([$reservationId]);
            $paiementIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($paiementIds as $paiementId) {
                // Supprimer les remboursements
                $stmt = $this->db->prepare("DELETE FROM remboursements WHERE paiement_id = ?");
                $stmt->execute([$paiementId]);
                
                // Supprimer les factures
                $stmt = $this->db->prepare("DELETE FROM factures WHERE paiement_id = ?");
                $stmt->execute([$paiementId]);
            }
            
            // Supprimer les paiements
            $stmt = $this->db->prepare("DELETE FROM paiements WHERE reservation_id = ?");
            $stmt->execute([$reservationId]);
        }
        
        // Marquer les places comme libres pour les réservations de l'utilisateur
        $stmt = $this->db->prepare("
            UPDATE parking_spaces 
            SET status = 'libre' 
            WHERE id IN (SELECT place_id FROM reservations WHERE user_id = ? AND status = 'confirmée')
        ");
        $stmt->execute([$userId]);
        
        // Supprimer les réservations
        $stmt = $this->db->prepare("DELETE FROM reservations WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Supprimer les logs
        $stmt = $this->db->prepare("DELETE FROM logs WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    public function listPlaces() {
        require_once 'backend/Services/PaginationService.php';
        
        try {
            // Récupérer les paramètres de pagination et de filtrage
            $currentPage = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT) ?: 1;
            $typeFilter = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?: '';
            $statusFilter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING) ?: '';
            $itemsPerPage = 10; // 10 places par page
            
            // Mettre à jour les statuts des places
            $this->updatePlacesStatus();
            
            // Construire la requête SQL de base pour le comptage
            $countSql = "SELECT COUNT(*) FROM parking_spaces";
            $params = [];
            
            // Construire la requête pour récupérer les places
            $sql = "SELECT parking_spaces.*, 
                    (SELECT COUNT(*) FROM reservations 
                     WHERE reservations.place_id = parking_spaces.id 
                     AND reservations.status = 'confirmée' 
                     AND reservations.date_fin > NOW()) as has_active_reservations
                    FROM parking_spaces";
            
            // Ajouter les filtres si spécifiés
            $whereClause = [];
            
            if ($typeFilter !== '') {
                $whereClause[] = "parking_spaces.type = ?";
                $params[] = $typeFilter;
            }
            
            if ($statusFilter !== '') {
                $whereClause[] = "parking_spaces.status = ?";
                $params[] = $statusFilter;
            }
            
            // Ajouter WHERE à la requête si nécessaire
            if (!empty($whereClause)) {
                $countSql .= " WHERE " . implode(' AND ', $whereClause);
                $sql .= " WHERE " . implode(' AND ', $whereClause);
            }
            
            // Exécuter la requête de comptage
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $totalPlaces = $stmt->fetchColumn();
            
            // Initialiser la pagination
            $pagination = new PaginationService($totalPlaces, $itemsPerPage, $currentPage);
            
            // Compléter la requête pour les places avec tri et pagination
            $sql .= " ORDER BY parking_spaces.numero LIMIT ?, ?";
            $queryParams = $params;
            $queryParams[] = $pagination->getOffset();
            $queryParams[] = $pagination->getLimit();
            
            // Exécuter la requête paginée
            $stmt = $this->db->prepare($sql);
            $stmt->execute($queryParams);
            $places = $stmt->fetchAll();
            
            // Récupérer tous les types et statuts pour les filtres
            $stmt = $this->db->query("SELECT DISTINCT type FROM parking_spaces ORDER BY type");
            $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $stmt = $this->db->query("SELECT DISTINCT status FROM parking_spaces ORDER BY status");
            $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Préparer les paramètres pour les liens de pagination (conserver les filtres)
            $queryParams = [
                'page' => 'admin',
                'action' => 'places'
            ];
            
            if ($typeFilter !== '') {
                $queryParams['type'] = $typeFilter;
            }
            if ($statusFilter !== '') {
                $queryParams['status'] = $statusFilter;
            }
            
            // Générer les liens de pagination en passant explicitement les paramètres
            $paginationLinks = $pagination->createLinks(BASE_URL, $queryParams);
            
            require_once 'frontend/Views/admin/places/list.php';
        } catch (Exception $e) {
            // Journaliser l'erreur pour le débogage
            error_log("Erreur dans AdminController::listPlaces : " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            $_SESSION['error'] = "Une erreur est survenue lors du filtrage des places: " . $e->getMessage();
            header('Location: ' . BASE_URL . '/?page=admin&action=places');
            exit;
        }
    }

    /**
     * Met à jour le statut des places en fonction des réservations actives
     */
    private function updatePlacesStatus() {
        // Marquer comme occupées les places avec des réservations actives actuellement (à l'instant présent)
        $this->db->query("
            UPDATE parking_spaces ps
            SET ps.status = 'occupe'
            WHERE ps.status = 'libre'
            AND EXISTS (
                SELECT 1 FROM reservations r
                WHERE r.place_id = ps.id
                AND r.status = 'confirmée'
                AND r.date_debut <= NOW()
                AND r.date_fin > NOW()
            )
        ");
        
        // Marquer comme libres les places sans réservations actives actuellement
        $this->db->query("
            UPDATE parking_spaces ps
            SET ps.status = 'libre'
            WHERE ps.status = 'occupe'
            AND NOT EXISTS (
                SELECT 1 FROM reservations r
                WHERE r.place_id = ps.id
                AND r.status = 'confirmée'
                AND r.date_debut <= NOW()
                AND r.date_fin > NOW()
            )
            AND ps.status != 'maintenance'
        ");
    }

    public function addPlace() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validation des données
                $numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_STRING);
                $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
                $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
                
                // Vérification que les données requises sont présentes
                if (empty($numero)) {
                    throw new Exception("Le numéro de place est obligatoire");
                }
                
                // Vérification que le numéro n'existe pas déjà
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM parking_spaces WHERE numero = ?");
                $stmt->execute([$numero]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("Une place avec ce numéro existe déjà");
                }
                
                // Valider le type
                $validTypes = ['standard', 'handicape', 'electrique'];
                if (!in_array($type, $validTypes)) {
                    $type = 'standard'; // Valeur par défaut
                }
                
                // Valider le statut
                $validStatuses = ['libre', 'occupe', 'maintenance'];
                if (!in_array($status, $validStatuses)) {
                    $status = 'libre'; // Valeur par défaut
                }
                
                // Insérer dans la base de données
                $stmt = $this->db->prepare("
                    INSERT INTO parking_spaces (numero, type, status) 
                    VALUES (?, ?, ?)
                ");
                
                if (!$stmt->execute([$numero, $type, $status])) {
                    throw new Exception("Erreur lors de l'insertion dans la base de données");
                }
                
                $_SESSION['success'] = "Place ajoutée avec succès";
                header('Location: ' . BASE_URL . '/?page=admin&action=places');
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                // Ne pas rediriger ici pour permettre de conserver les données du formulaire
            }
        }
        
        require_once 'frontend/Views/admin/places/add.php';
    }

    public function editPlace() {
        $id = $_GET['id'] ?? null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numero = $_POST['numero'] ?? '';
            $type = $_POST['type'] ?? 'standard';
            $status = $_POST['status'] ?? 'libre';
            
            $stmt = $this->db->prepare("
                UPDATE parking_spaces 
                SET numero = ?, type = ?, status = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$numero, $type, $status, $id])) {
                $_SESSION['success'] = "Place mise à jour avec succès";
                header('Location: ' . BASE_URL . '/?page=admin&action=places');
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de la place";
            }
        }
        
        $stmt = $this->db->prepare("SELECT * FROM parking_spaces WHERE id = ?");
        $stmt->execute([$id]);
        $place = $stmt->fetch();
        
        require_once 'frontend/Views/admin/places/edit.php';
    }

    public function deletePlace() {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $force = filter_input(INPUT_GET, 'force', FILTER_VALIDATE_BOOLEAN);
        
        if (!$id) {
            $_SESSION['error'] = "ID de place invalide";
            header('Location: ' . BASE_URL . '/?page=admin&action=places');
            exit;
        }
        
        try {
            // Vérifier que la place existe
            $stmt = $this->db->prepare("SELECT numero, type FROM parking_spaces WHERE id = ?");
            $stmt->execute([$id]);
            $place = $stmt->fetch();
            
            if (!$place) {
                throw new Exception("Place non trouvée");
            }
            
            // Récupérer les réservations associées
            $stmt = $this->db->prepare("
                SELECT r.*, ps.numero, ps.type 
                FROM reservations r
                JOIN parking_spaces ps ON r.place_id = ps.id
                WHERE r.place_id = ?
                ORDER BY r.date_debut DESC
            ");
            $stmt->execute([$id]);
            $reservations = $stmt->fetchAll();
            $reservationCount = count($reservations);
            
            // Vérifier si des réservations non-annulées existent
            $activeReservations = array_filter($reservations, function($r) {
                return $r['status'] !== 'annulée';
            });
            
            // Si des réservations actives existent et qu'on ne force pas la suppression, afficher un message
            if (count($activeReservations) > 0 && !$force) {
                // Stocker les données des réservations dans la session pour l'affichage
                $_SESSION['reservations_to_delete'] = $reservations;
                $_SESSION['place_to_delete'] = $place;
                
                $_SESSION['warning'] = "Cette place possède " . count($activeReservations) . " réservation(s) active(s). <a href='" . BASE_URL . "/?page=admin&action=deletePlace&id=$id&force=1' class='alert-link'>Cliquer ici</a> pour supprimer la place et toutes ses réservations.";
                header('Location: ' . BASE_URL . '/?page=admin&action=confirmDeletePlace&id=' . $id);
                exit;
            }
            
            $this->db->beginTransaction();
            
            // Si des réservations existent (même annulées), supprimer les données associées
            if ($reservationCount > 0) {
                // Récupérer les IDs des réservations
                $stmt = $this->db->prepare("SELECT id FROM reservations WHERE place_id = ?");
                $stmt->execute([$id]);
                $reservationIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($reservationIds as $reservationId) {
                    // Récupérer les IDs des paiements
                    $stmt = $this->db->prepare("SELECT id FROM paiements WHERE reservation_id = ?");
                    $stmt->execute([$reservationId]);
                    $paiementIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    foreach ($paiementIds as $paiementId) {
                        // Supprimer les remboursements associés
                        $stmt = $this->db->prepare("DELETE FROM remboursements WHERE paiement_id = ?");
                        $stmt->execute([$paiementId]);
                        
                        // Supprimer les factures associées
                        $stmt = $this->db->prepare("DELETE FROM factures WHERE paiement_id = ?");
                        $stmt->execute([$paiementId]);
                    }
                    
                    // Supprimer les paiements associés
                    $stmt = $this->db->prepare("DELETE FROM paiements WHERE reservation_id = ?");
                    $stmt->execute([$reservationId]);
                    
                    // Supprimer les notifications associées à cette réservation
                    $stmt = $this->db->prepare("
                        DELETE FROM notifications 
                        WHERE message LIKE ? OR message LIKE ?
                    ");
                    $stmt->execute([
                        "%place n°{$place['numero']}%réservation #{$reservationId}%",
                        "%réservation #{$reservationId}%"
                    ]);
                }
                
                // Supprimer toutes les réservations
                $stmt = $this->db->prepare("DELETE FROM reservations WHERE place_id = ?");
                $stmt->execute([$id]);
                
                $_SESSION['info'] = "$reservationCount réservation(s) supprimée(s)";
            }
            
            // Enfin, supprimer la place
            $stmt = $this->db->prepare("DELETE FROM parking_spaces WHERE id = ?");
            if (!$stmt->execute([$id])) {
                throw new Exception("Erreur lors de la suppression de la place");
            }
            
            $this->db->commit();
            $_SESSION['success'] = "Place n°{$place['numero']} supprimée avec succès";
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/?page=admin&action=places');
        exit;
    }

    public function confirmDeletePlace() {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$id) {
            $_SESSION['error'] = "ID de place invalide";
            header('Location: ' . BASE_URL . '/?page=admin&action=places');
            exit;
        }
        
        // Les réservations sont récupérées par deletePlace et stockées dans la session
        $reservations = $_SESSION['reservations_to_delete'] ?? [];
        $place = $_SESSION['place_to_delete'] ?? null;
        
        require_once 'frontend/Views/admin/places/confirm_delete.php';
    }

    public function manageRefunds() {
        $stmt = $this->db->query("
            SELECT r.*, 
                   p.montant as paiement_montant,
                   res.date_debut, res.date_fin,
                   ps.numero as place_numero,
                   u.nom, u.prenom, u.email
            FROM remboursements r
            JOIN paiements p ON r.paiement_id = p.id
            JOIN reservations res ON p.reservation_id = res.id
            JOIN parking_spaces ps ON res.place_id = ps.id
            JOIN users u ON res.user_id = u.id
            ORDER BY r.date_demande DESC
        ");
        $remboursements = $stmt->fetchAll();
        
        require_once 'frontend/Views/admin/refunds.php';
    }

    public function processRefund() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['remboursement_id'] ?? null;
            $decision = $_POST['decision'] ?? null;
            $commentaireAdmin = $_POST['commentaire_admin'] ?? '';
            
            // Valider le commentaire pour le refus
            if ($decision === 'refuse' && empty($commentaireAdmin)) {
                $_SESSION['error'] = "Vous devez indiquer un motif de refus";
                header('Location: ' . BASE_URL . '/?page=admin&action=refunds');
                exit;
            }
            
            if ($id && in_array($decision, ['accepte', 'refuse'])) {
                $this->db->beginTransaction();
                
                try {
                    // Récupérer les informations de remboursement et de paiement associé
                    $stmt = $this->db->prepare("
                        SELECT r.*, p.reservation_id, res.place_id, res.date_debut, res.date_fin, res.user_id,
                               u.email, u.prenom, u.nom, ps.numero as place_numero
                        FROM remboursements r
                        JOIN paiements p ON r.paiement_id = p.id
                        JOIN reservations res ON p.reservation_id = res.id
                        JOIN users u ON res.user_id = u.id
                        JOIN parking_spaces ps ON res.place_id = ps.id
                        WHERE r.id = ?
                    ");
                    $stmt->execute([$id]);
                    $remboursement = $stmt->fetch();
                    
                    if (!$remboursement) {
                        throw new Exception("Remboursement non trouvé");
                    }
                    
                    // Mettre à jour le statut du remboursement et le commentaire
                    $status = ($decision === 'accepte') ? 'effectué' : 'refusé';
                    $stmt = $this->db->prepare("
                        UPDATE remboursements 
                        SET status = ?, commentaire_admin = ?
                        WHERE id = ?
                    ");
                    
                    if (!$stmt->execute([$status, $commentaireAdmin, $id])) {
                        throw new Exception("Erreur lors de la mise à jour du remboursement");
                    }
                    
                    // Créer une notification pour l'utilisateur
                    require_once 'backend/Services/NotificationService.php';
                    $notificationService = new NotificationService();
                    
                    if ($decision === 'accepte') {
                        // Si le remboursement est accepté
                        $messageNotif = "Votre demande de remboursement de {$remboursement['montant']}€ pour la réservation de la place n°{$remboursement['place_numero']} a été acceptée.";
                        if (!empty($commentaireAdmin)) {
                            $messageNotif .= " Commentaire: " . $commentaireAdmin;
                        }
                        
                        // Vérifier si des alertes doivent être déclenchées
                        require_once 'backend/Services/AlerteDisponibiliteService.php';
                        $alerteService = new AlerteDisponibiliteService();
                        $alertesSent = $alerteService->checkAlertsForCancellation(
                            $remboursement['place_id'], 
                            $remboursement['date_debut'], 
                            $remboursement['date_fin']
                        );
                        
                        $message = "Demande de remboursement acceptée.";
                        if ($alertesSent > 0) {
                            $message .= " " . $alertesSent . " utilisateur(s) ont été notifiés de la disponibilité du créneau libéré.";
                        }
                        $_SESSION['success'] = $message;
                    } else {
                        // Si le remboursement est refusé
                        $messageNotif = "Votre demande de remboursement de {$remboursement['montant']}€ pour la réservation de la place n°{$remboursement['place_numero']} a été refusée.";
                        if (!empty($commentaireAdmin)) {
                            $messageNotif .= " Motif: " . $commentaireAdmin;
                        }
                        $_SESSION['success'] = "Demande de remboursement refusée.";
                    }
                    
                    // Créer la notification
                    $notificationService->createNotification(
                        $remboursement['user_id'],
                        'Réponse à votre demande de remboursement',
                        $messageNotif,
                        'system'
                    );
                    
                    // Envoyer un email à l'utilisateur
                    if (class_exists('EmailService')) {
                        require_once 'backend/Services/EmailService.php';
                        $emailService = new EmailService();
                        
                        $sujet = "Votre demande de remboursement - " . ($decision === 'accepte' ? 'Acceptée' : 'Refusée');
                        $message = "<h1>" . ($decision === 'accepte' ? 'Remboursement accepté' : 'Remboursement refusé') . "</h1>";
                        $message .= "<p>Bonjour {$remboursement['prenom']},</p>";
                        $message .= "<p>Votre demande de remboursement de <strong>{$remboursement['montant']}€</strong> pour la réservation de la place n°{$remboursement['place_numero']} ";
                        $message .= "du " . date('d/m/Y H:i', strtotime($remboursement['date_debut'])) . " au " . date('d/m/Y H:i', strtotime($remboursement['date_fin'])) . " a été ";
                        $message .= $decision === 'accepte' ? 'acceptée' : 'refusée';
                        $message .= ".</p>";
                        
                        if (!empty($commentaireAdmin)) {
                            $message .= "<p><strong>Commentaire de l'administrateur :</strong> " . htmlspecialchars($commentaireAdmin) . "</p>";
                        }
                        
                        $message .= "<p>Cordialement,<br>L'équipe Parkme In</p>";
                        
                        $emailService->sendEmail($remboursement['email'], $sujet, $message);
                    }
                    
                    $this->db->commit();
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $_SESSION['error'] = $e->getMessage();
                }
            } else {
                $_SESSION['error'] = "Paramètres invalides";
            }
        }
        
        header('Location: ' . BASE_URL . '/?page=admin&action=refunds');
        exit;
    }

    public function refundStats() {
        // Statistiques des remboursements par mois
        $monthlyStats = $this->db->query("
            SELECT 
                DATE_FORMAT(date_demande, '%Y-%m') as mois,
                COUNT(*) as total_demandes,
                SUM(CASE WHEN status = 'effectué' THEN 1 ELSE 0 END) as acceptes,
                SUM(CASE WHEN status = 'refusé' THEN 1 ELSE 0 END) as refuses,
                SUM(CASE WHEN status = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
                SUM(montant) as montant_total
            FROM remboursements
            GROUP BY mois
            ORDER BY mois DESC
        ")->fetchAll();
        
        // Statistiques par motif de remboursement
        $reasonStats = $this->db->query("
            SELECT 
                raison,
                COUNT(*) as nombre,
                SUM(CASE WHEN status = 'effectué' THEN 1 ELSE 0 END) as acceptes,
                AVG(montant) as montant_moyen
            FROM remboursements
            GROUP BY raison
            ORDER BY nombre DESC
        ")->fetchAll();
        
        // Préparer les données pour les graphiques
        $chartData = [
            'months' => [],
            'counts' => [],
            'amounts' => []
        ];
        
        foreach ($monthlyStats as $stat) {
            $chartData['months'][] = $stat['mois'];
            $chartData['counts'][] = $stat['total_demandes'];
            $chartData['amounts'][] = $stat['montant_total'];
        }
        
        // Ajouter les scripts JS pour les graphiques
        $extraJS = ['refund-stats.js'];
        
        // Charger la vue
        require_once 'frontend/Views/admin/refund_stats.php';
    }
    
    public function manageTarifs() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                
                // Mettre à jour les tarifs pour chaque type de place
                $types = ['standard', 'handicape', 'electrique'];
                
                foreach ($types as $type) {
                    $prixHeure = filter_input(INPUT_POST, "prix_heure_$type", FILTER_VALIDATE_FLOAT);
                    $prixJournee = filter_input(INPUT_POST, "prix_journee_$type", FILTER_VALIDATE_FLOAT);
                    $prixMois = filter_input(INPUT_POST, "prix_mois_$type", FILTER_VALIDATE_FLOAT);
                    
                    if ($prixHeure === false || $prixJournee === false || $prixMois === false) {
                        throw new Exception("Valeurs de prix invalides pour le type $type");
                    }
                    
                    $stmt = $this->db->prepare("
                        UPDATE tarifs 
                        SET prix_heure = ?, prix_journee = ?, prix_mois = ? 
                        WHERE type_place = ?
                    ");
                    
                    if (!$stmt->execute([$prixHeure, $prixJournee, $prixMois, $type])) {
                        throw new Exception("Erreur lors de la mise à jour des tarifs pour le type $type");
                    }
                }
                
                $this->db->commit();
                $_SESSION['success'] = "Tarifs mis à jour avec succès";
            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        // Récupérer les tarifs actuels
        $stmt = $this->db->query("SELECT * FROM tarifs ORDER BY type_place");
        $tarifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organiser les tarifs par type pour un accès plus facile dans la vue
        $tarifsByType = [];
        foreach ($tarifs as $tarif) {
            $tarifsByType[$tarif['type_place']] = $tarif;
        }
        
        require_once 'frontend/Views/admin/tarifs.php';
    }
    
    public function manageHoraires() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                
                // Récupérer et valider les données des horaires pour chaque jour
                for ($jour = 1; $jour <= 7; $jour++) {
                    $heureOuverture = filter_input(INPUT_POST, "ouverture_$jour", FILTER_SANITIZE_STRING);
                    $heureFermeture = filter_input(INPUT_POST, "fermeture_$jour", FILTER_SANITIZE_STRING);
                    
                    // Valider les formats d'heure
                    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $heureOuverture) || 
                        !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $heureFermeture)) {
                        throw new Exception("Format d'heure invalide pour le jour $jour");
                    }
                    
                    // Vérifier que l'heure de fermeture est après l'heure d'ouverture
                    if (strtotime($heureFermeture) <= strtotime($heureOuverture)) {
                        throw new Exception("L'heure de fermeture doit être après l'heure d'ouverture pour le jour $jour");
                    }
                    
                    // Mettre à jour les horaires
                    $stmt = $this->db->prepare("
                        UPDATE horaires_ouverture 
                        SET heure_ouverture = ?, heure_fermeture = ? 
                        WHERE jour_semaine = ?
                    ");
                    
                    if (!$stmt->execute([$heureOuverture, $heureFermeture, $jour])) {
                        throw new Exception("Erreur lors de la mise à jour des horaires pour le jour $jour");
                    }
                }
                
                $this->db->commit();
                $_SESSION['success'] = "Horaires mis à jour avec succès";
            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        // Récupérer les horaires actuels
        $stmt = $this->db->query("SELECT * FROM horaires_ouverture ORDER BY jour_semaine");
        $horaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organiser les horaires par jour pour un accès plus facile dans la vue
        $horairesByDay = [];
        foreach ($horaires as $horaire) {
            $horairesByDay[$horaire['jour_semaine']] = $horaire;
        }
        
        // Noms des jours pour l'affichage
        $joursNoms = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche'
        ];
        
        require_once 'frontend/Views/admin/horaires.php';
    }

    /**
     * Change le statut d'une place de parking
     */
    public function changeStatus() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }
        
        try {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            
            if (!$id || !in_array($status, ['libre', 'occupe', 'maintenance'])) {
                throw new Exception("Paramètres invalides");
            }
            
            // Vérifier si la place existe
            $stmt = $this->db->prepare("SELECT numero, status FROM parking_spaces WHERE id = ?");
            $stmt->execute([$id]);
            $place = $stmt->fetch();
            
            if (!$place) {
                throw new Exception("Place non trouvée");
            }
            
            // Pour passer en maintenance, vérifier s'il y a des réservations actives
            if ($status === 'maintenance') {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM reservations 
                    WHERE place_id = ? 
                    AND status = 'confirmée' 
                    AND date_fin > NOW()
                ");
                $stmt->execute([$id]);
                $activeReservations = $stmt->fetchColumn();
                
                if ($activeReservations > 0 && (!isset($_POST['force']) || $_POST['force'] != 1)) {
                    echo json_encode([
                        'success' => false, 
                        'needConfirmation' => true,
                        'message' => "Cette place a $activeReservations réservation(s) active(s). Êtes-vous sûr de vouloir la mettre en maintenance?"
                    ]);
                    exit;
                }
            }
            
            // Mettre à jour le statut
            $stmt = $this->db->prepare("UPDATE parking_spaces SET status = ? WHERE id = ?");
            if (!$stmt->execute([$status, $id])) {
                throw new Exception("Erreur lors de la mise à jour du statut");
            }
            
            // Si passage en maintenance, annuler les réservations futures
            if ($status === 'maintenance' && isset($_POST['force']) && $_POST['force'] == 1) {
                $this->cancelFutureReservations($id);
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Statut de la place n°{$place['numero']} changé en «" . ucfirst($status) . "»"
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit;
    }

    /**
     * Annule les réservations futures d'une place mise en maintenance
     * 
     * @param int $placeId ID de la place
     * @return int Nombre de réservations annulées
     */
    private function cancelFutureReservations($placeId) {
        // Récupérer les informations sur la place
        $stmt = $this->db->prepare("SELECT numero FROM parking_spaces WHERE id = ?");
        $stmt->execute([$placeId]);
        $place = $stmt->fetch();
        
        if (!$place) return false;
        
        // Récupérer les réservations confirmées futures
        $stmt = $this->db->prepare("
            SELECT r.*, u.id as user_id 
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            WHERE r.place_id = ? 
            AND r.status = 'confirmée' 
            AND r.date_fin > NOW()
        ");
        $stmt->execute([$placeId]);
        $reservations = $stmt->fetchAll();
        
        foreach ($reservations as $reservation) {
            // Annuler la réservation
            $stmt = $this->db->prepare("UPDATE reservations SET status = 'annulée' WHERE id = ?");
            $stmt->execute([$reservation['id']]);
            
            // Créer une notification
            require_once 'backend/Services/NotificationService.php';
            $notificationService = new NotificationService();
            
            $notificationService->createNotification(
                $reservation['user_id'],
                'Réservation annulée - Maintenance',
                "Votre réservation de la place n°{$place['numero']} a été annulée car cette place est mise en maintenance.",
                'system'
            );
        }
        
        return count($reservations);
    }
}