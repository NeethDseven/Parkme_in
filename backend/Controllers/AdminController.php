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
        
        require_once 'frontend/Views/admin/dashboard.php';
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
        
        // Récupérer les paramètres de pagination et de filtrage
        $currentPage = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT) ?: 1;
        $typeFilter = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
        $statusFilter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
        $itemsPerPage = 10; // 10 places par page
        
        // Mettre à jour les statuts des places
        $this->updatePlacesStatus();
        
        // Construire la requête SQL de base pour le comptage
        $countSql = "SELECT COUNT(*) FROM parking_spaces";
        $params = [];
        
        // Construire la requête pour récupérer les places
        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM reservations r 
                 WHERE r.place_id = p.id 
                 AND r.status = 'confirmée' 
                 AND r.date_fin > NOW()) as has_active_reservations
                FROM parking_spaces p";
        
        // Ajouter les filtres si spécifiés
        $whereClause = [];
        
        if ($typeFilter) {
            $whereClause[] = "p.type = ?";
            $params[] = $typeFilter;
        }
        
        if ($statusFilter) {
            $whereClause[] = "p.status = ?";
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
        $sql .= " ORDER BY p.numero LIMIT ?, ?";
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
        
        if ($typeFilter) {
            $queryParams['type'] = $typeFilter;
        }
        if ($statusFilter) {
            $queryParams['status'] = $statusFilter;
        }
        
        // Générer les liens de pagination en passant explicitement 'page' et 'action'
        $paginationLinks = $pagination->createLinks(BASE_URL, $queryParams);
        
        require_once 'frontend/Views/admin/places/list.php';
    }

    /**
     * Met à jour le statut des places en fonction des réservations actives
     */
    private function updatePlacesStatus() {
        // Marquer comme occupées les places avec des réservations actives
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
        
        // Marquer comme libres les places sans réservations actives
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
            
            // Si des réservations existent et qu'on ne force pas la suppression, afficher un message
            if ($reservationCount > 0 && !$force) {
                // Stocker les données des réservations dans la session pour l'affichage
                $_SESSION['reservations_to_delete'] = $reservations;
                $_SESSION['place_to_delete'] = $place;
                
                $_SESSION['warning'] = "Cette place possède $reservationCount réservation(s) associée(s). <a href='" . BASE_URL . "/?page=admin&action=deletePlace&id=$id&force=1' class='alert-link'>Cliquer ici</a> pour supprimer la place et toutes ses réservations.";
                header('Location: ' . BASE_URL . '/?page=admin&action=confirmDeletePlace&id=' . $id);
                exit;
            }
            
            $this->db->beginTransaction();
            
            // Si on force la suppression, supprimer toutes les données associées
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
                
                $_SESSION['info'] = "$reservationCount réservation(s) et toutes les données associées ont été supprimées";
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
            
            if ($id && in_array($decision, ['accepte', 'refuse'])) {
                $stmt = $this->db->prepare("
                    UPDATE remboursements 
                    SET status = ? 
                    WHERE id = ?
                ");
                
                $status = ($decision === 'accepte') ? 'effectué' : 'refusé';
                
                if ($stmt->execute([$status, $id])) {
                    $_SESSION['success'] = "Demande de remboursement " . ($status === 'effectué' ? 'acceptée' : 'refusée');
                } else {
                    $_SESSION['error'] = "Erreur lors du traitement de la demande";
                }
            }
        }
        
        header('Location: ' . BASE_URL . '/?page=admin&action=refunds');
        exit;
    }
}