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
        
        require_once 'app/Views/admin/dashboard.php';
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
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();
        require_once 'app/Views/admin/users/list.php';
    }

    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $telephone = $_POST['telephone'] ?? '';
            $role = $_POST['role'] ?? 'user';
            
            // Validation
            if (empty($email) || empty($password) || empty($nom)) {
                $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
                require_once 'app/Views/admin/users/add.php';
                return;
            }
            
            // Vérifier si l'email existe déjà
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Cet email est déjà utilisé.";
                require_once 'app/Views/admin/users/add.php';
                return;
            }
            
            // Hachage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Création de l'utilisateur
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password, nom, prenom, telephone, role)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$email, $hashedPassword, $nom, $prenom, $telephone, $role])) {
                $_SESSION['success'] = "Utilisateur créé avec succès.";
                header('Location: ' . BASE_URL . '/?page=admin&action=users');
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la création de l'utilisateur.";
            }
        }
        
        require_once 'app/Views/admin/users/add.php';
    }

    public function editUser() {
        $id = $_GET['id'] ?? null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? 'user';
            
            $stmt = $this->db->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, role = ? WHERE id = ?");
            if ($stmt->execute([$nom, $prenom, $email, $role, $id])) {
                header('Location: ' . BASE_URL . '/?page=admin&action=users');
                exit;
            }
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        require_once 'app/Views/admin/users/edit.php';
    }

    public function deleteUser() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            try {
                $this->db->beginTransaction();
                
                // Supprimer d'abord les paiements liés aux réservations de l'utilisateur
                $stmt = $this->db->prepare("
                    DELETE FROM paiements 
                    WHERE reservation_id IN (
                        SELECT id FROM reservations WHERE user_id = ?
                    )
                ");
                $stmt->execute([$id]);
                
                // Supprimer ensuite les réservations de l'utilisateur
                $stmt = $this->db->prepare("DELETE FROM reservations WHERE user_id = ?");
                $stmt->execute([$id]);
                
                // Enfin, supprimer l'utilisateur lui-même
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                
                $this->db->commit();
                $_SESSION['success'] = "L'utilisateur a été supprimé avec succès.";
            } catch (PDOException $e) {
                $this->db->rollBack();
                $_SESSION['error'] = "Impossible de supprimer cet utilisateur car il a des données associées.";
            }
        }
        header('Location: ' . BASE_URL . '/?page=admin&action=users');
        exit;
    }

    public function listPlaces() {
        $stmt = $this->db->query("SELECT * FROM parking_spaces ORDER BY numero");
        $places = $stmt->fetchAll();
        require_once 'app/Views/admin/places/list.php';
    }

    public function addPlace() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numero = $_POST['numero'];
            $type = $_POST['type'];
            $stmt = $this->db->prepare("INSERT INTO parking_spaces (numero, type) VALUES (?, ?)");
            if ($stmt->execute([$numero, $type])) {
                header('Location: ' . BASE_URL . '/?page=admin&action=places');
                exit;
            }
        }
        require_once 'app/Views/admin/places/add.php';
    }

    public function editPlace() {
        $id = $_GET['id'] ?? null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numero = $_POST['numero'];
            $type = $_POST['type'];
            $status = $_POST['status'];
            
            $stmt = $this->db->prepare("UPDATE parking_spaces SET numero = ?, type = ?, status = ? WHERE id = ?");
            if ($stmt->execute([$numero, $type, $status, $id])) {
                header('Location: ' . BASE_URL . '/?page=admin&action=places');
                exit;
            }
        }

        $stmt = $this->db->prepare("SELECT * FROM parking_spaces WHERE id = ?");
        $stmt->execute([$id]);
        $place = $stmt->fetch();
        require_once 'app/Views/admin/places/edit.php';
    }

    public function deletePlace() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM parking_spaces WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: ' . BASE_URL . '/?page=admin&action=places');
        exit;
    }

    public function refundStats() {
        $stats = [
            'total' => $this->getRefundCount(),
            'montant_total' => $this->getRefundTotal(),
            'moyenne' => $this->getRefundAverage(),
            'par_mois' => $this->getRefundsByMonth()
        ];
        
        require_once 'app/Views/admin/refund_stats.php';
    }

    private function getRefundCount() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM remboursements");
        return $stmt->fetchColumn();
    }

    private function getRefundTotal() {
        $stmt = $this->db->query("
            SELECT SUM(montant) 
            FROM remboursements 
            WHERE status = 'effectué'
        ");
        return $stmt->fetchColumn() ?? 0;
    }

    private function getRefundAverage() {
        $stmt = $this->db->query("
            SELECT AVG(montant) 
            FROM remboursements 
            WHERE status = 'effectué'
        ");
        return round($stmt->fetchColumn() ?? 0, 2);
    }

    private function getRefundsByMonth() {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(date_demande, '%Y-%m') as mois,
                   COUNT(*) as total,
                   SUM(montant) as montant_total
            FROM remboursements
            GROUP BY mois
            ORDER BY mois DESC
            LIMIT 12
        ");
        return $stmt->fetchAll();
    }

    public function manageRefunds() {
        $stmt = $this->db->query("
            SELECT r.*, p.montant as montant_initial, 
                   u.nom, u.prenom, u.email,
                   res.date_debut, res.date_fin,
                   ps.numero as place_numero
            FROM remboursements r
            JOIN paiements p ON r.paiement_id = p.id
            JOIN reservations res ON p.reservation_id = res.id
            JOIN users u ON res.user_id = u.id
            JOIN parking_spaces ps ON res.place_id = ps.id
            ORDER BY r.date_demande DESC
        ");
        $remboursements = $stmt->fetchAll();
        require_once 'app/Views/admin/refunds.php';
    }

    public function processRefund() {
        try {
            $id = $_POST['remboursement_id'] ?? null;
            $decision = $_POST['decision'] ?? null;

            if (!$id || !$decision) {
                throw new Exception("Paramètres manquants");
            }

            $status = ($decision === 'accepte') ? 'effectué' : 'refusé';
            
            $stmt = $this->db->prepare("
                UPDATE remboursements 
                SET status = ? 
                WHERE id = ?
            ");
            $stmt->execute([$status, $id]);
            
            // Notifier l'utilisateur du traitement de son remboursement
            $stmt = $this->db->prepare("
                SELECT r.montant, r.paiement_id, p.reservation_id, res.user_id
                FROM remboursements r
                JOIN paiements p ON r.paiement_id = p.id
                JOIN reservations res ON p.reservation_id = res.id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            
            if ($data) {
                require_once 'app/Services/NotificationService.php';
                $notificationService = new NotificationService();
                
                $message = $decision === 'accepte' 
                    ? "Votre demande de remboursement de {$data['montant']}€ a été approuvée."
                    : "Votre demande de remboursement a été refusée. Veuillez nous contacter pour plus d'informations.";
                
                $notificationService->createNotification(
                    $data['user_id'],
                    'Traitement de remboursement',
                    $message,
                    'remboursement'
                );
            }

            $_SESSION['success'] = "Remboursement traité avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/?page=admin&action=refunds');
        exit;
    }

    public function manageTarifs() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateTarifs($_POST);
        }
        
        $stmt = $this->db->query("SELECT * FROM tarifs");
        $tarifs = $stmt->fetchAll();
        require_once 'app/Views/admin/tarifs.php';
    }

    private function updateTarifs($data) {
        foreach ($data['tarifs'] as $id => $tarif) {
            $stmt = $this->db->prepare("
                UPDATE tarifs 
                SET prix_heure = ?, prix_journee = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $tarif['prix_heure'],
                $tarif['prix_journee'],
                $id
            ]);
        }
    }

    public function getRealTimeStats() {
        $stats = [
            'places_occupees' => $this->getOccupiedSpaces(),
            'revenus_jour' => $this->getDailyRevenue(),
            'reservations_attente' => $this->getPendingReservations()
        ];
        header('Content-Type: application/json');
        echo json_encode($stats);
    }

    private function getOccupiedSpaces() {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM parking_spaces 
            WHERE status = 'occupe'
        ");
        return $stmt->fetchColumn();
    }

    private function getDailyRevenue() {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(montant), 0)
            FROM paiements
            WHERE status = 'valide'
            AND DATE(date_paiement) = CURDATE()
        ");
        return $stmt->fetchColumn();
    }

    private function getPendingReservations() {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM reservations
            WHERE status = 'en_attente'
        ");
        return $stmt->fetchColumn();
    }

    public function manageHoraires() {
        require_once 'app/Models/HorairesModel.php';
        $horairesModel = new \App\Models\HorairesModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $jourSemaine = $_POST['jour_semaine'];
            $heureOuverture = $_POST['heure_ouverture'];
            $heureFermeture = $_POST['heure_fermeture'];
            
            if ($horairesModel->updateHoraires($jourSemaine, $heureOuverture, $heureFermeture)) {
                $_SESSION['success'] = "Horaires mis à jour avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour des horaires";
            }
        }
        
        $horaires = $horairesModel->getHorairesOuverture();
        require_once 'app/Views/admin/horaires.php';
    }

    public function listReservations() {
        // Récupérer toutes les réservations avec informations associées
        $stmt = $this->db->query("
            SELECT r.*, 
                   u.nom, u.prenom, u.email,
                   ps.numero as place_numero, ps.type as place_type,
                   p.montant, p.status as payment_status, p.date_paiement
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            JOIN parking_spaces ps ON r.place_id = ps.id
            LEFT JOIN paiements p ON p.reservation_id = r.id
            ORDER BY r.date_debut DESC
        ");
        $reservations = $stmt->fetchAll();
        
        require_once 'app/Views/admin/reservations/list.php';
    }

    public function addReservation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_POST['user_id'];
                $placeId = $_POST['place_id'];
                $dateDebut = $_POST['date_debut'];
                $dateFin = $_POST['date_fin'];
                $status = $_POST['status'] ?? 'en_attente';
                
                // Validation basique
                if (empty($userId) || empty($placeId) || empty($dateDebut) || empty($dateFin)) {
                    throw new Exception("Tous les champs sont obligatoires");
                }
                
                // Vérifier que la place est disponible sur la période
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM reservations
                    WHERE place_id = ?
                    AND status = 'confirmée'
                    AND (
                        (date_debut BETWEEN ? AND ?) OR
                        (date_fin BETWEEN ? AND ?) OR
                        (date_debut <= ? AND date_fin >= ?)
                    )
                ");
                $stmt->execute([$placeId, $dateDebut, $dateFin, $dateDebut, $dateFin, $dateDebut, $dateFin]);
                
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("Cette place est déjà réservée sur cette période");
                }
                
                // Création de la réservation
                $stmt = $this->db->prepare("
                    INSERT INTO reservations (user_id, place_id, date_debut, date_fin, status, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                // Debug - Afficher les valeurs avant l'insertion
                error_log("Données pour insertion: user_id=$userId, place_id=$placeId, date_debut=$dateDebut, date_fin=$dateFin, status=$status");
                
                if ($stmt->execute([$userId, $placeId, $dateDebut, $dateFin, $status])) {
                    $reservationId = $this->db->lastInsertId();
                    error_log("Réservation créée avec succès, ID: $reservationId");
                    
                    $_SESSION['success'] = "Réservation créée avec succès";
                    
                    // Si le statut est 'confirmée', mettre à jour le statut de la place
                    if ($status === 'confirmée') {
                        $stmt = $this->db->prepare("UPDATE parking_spaces SET status = 'occupe' WHERE id = ?");
                        $stmt->execute([$placeId]);
                        error_log("Statut de la place $placeId mis à jour: occupe");
                    }
                    
                    // Créer une notification pour l'utilisateur
                    require_once 'app/Services/NotificationService.php';
                    $notificationService = new NotificationService();
                    $notificationService->createNotification(
                        $userId,
                        'Nouvelle réservation',
                        "Une réservation a été créée pour vous par l'administrateur. Statut : $status",
                        'reservation'
                    );
                    
                    header('Location: ' . BASE_URL . '/?page=admin&action=reservations');
                    exit;
                } else {
                    throw new Exception("Erreur lors de la création de la réservation");
                }
            } catch (Exception $e) {
                error_log("Erreur lors de la création de la réservation: " . $e->getMessage());
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        // Récupérer la liste des utilisateurs pour le formulaire
        $stmt = $this->db->query("SELECT id, nom, prenom, email FROM users ORDER BY nom, prenom");
        $users = $stmt->fetchAll();
        
        // Récupérer la liste des places disponibles
        $stmt = $this->db->query("SELECT id, numero, type FROM parking_spaces WHERE status = 'libre' ORDER BY numero");
        $places = $stmt->fetchAll();
        
        require_once 'app/Views/admin/reservations/add.php';
    }

    public function editReservation() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "Identifiant de réservation manquant";
            header('Location: ' . BASE_URL . '/?page=admin&action=reservations');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $placeId = $_POST['place_id'];
                $dateDebut = $_POST['date_debut'];
                $dateFin = $_POST['date_fin'];
                $status = $_POST['status'];
                $oldStatus = $_POST['old_status'];
                
                // Si le statut passe à 'confirmée', vérifier la disponibilité
                if ($status === 'confirmée' && $oldStatus !== 'confirmée') {
                    // Vérifier les conflits
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) FROM reservations
                        WHERE place_id = ?
                        AND status = 'confirmée'
                        AND id != ?
                        AND (
                            (date_debut BETWEEN ? AND ?) OR
                            (date_fin BETWEEN ? AND ?) OR
                            (date_debut <= ? AND date_fin >= ?)
                        )
                    ");
                    $stmt->execute([$placeId, $id, $dateDebut, $dateFin, $dateDebut, $dateFin, $dateDebut, $dateFin]);
                    
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception("Cette place est déjà réservée sur cette période");
                    }
                    
                    // Mettre à jour l'état de la place
                    $stmt = $this->db->prepare("UPDATE parking_spaces SET status = 'occupe' WHERE id = ?");
                    $stmt->execute([$placeId]);
                }
                
                // Si le statut passe à 'annulée', libérer la place
                if ($status === 'annulée' && $oldStatus === 'confirmée') {
                    $stmt = $this->db->prepare("UPDATE parking_spaces SET status = 'libre' WHERE id = ?");
                    $stmt->execute([$placeId]);
                }
                
                // Mise à jour de la réservation
                $stmt = $this->db->prepare("
                    UPDATE reservations 
                    SET place_id = ?, date_debut = ?, date_fin = ?, status = ?
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$placeId, $dateDebut, $dateFin, $status, $id])) {
                    $_SESSION['success'] = "Réservation mise à jour avec succès";
                    
                    // Notifier l'utilisateur du changement de statut
                    if ($status !== $oldStatus) {
                        // Récupérer l'id utilisateur
                        $stmt = $this->db->prepare("SELECT user_id FROM reservations WHERE id = ?");
                        $stmt->execute([$id]);
                        $userId = $stmt->fetchColumn();
                        
                        require_once 'app/Services/NotificationService.php';
                        $notificationService = new NotificationService();
                        $notificationService->createNotification(
                            $userId,
                            'Modification de réservation',
                            "Le statut de votre réservation a été modifié de '$oldStatus' à '$status'.",
                            'reservation'
                        );
                    }
                    
                    header('Location: ' . BASE_URL . '/?page=admin&action=reservations');
                    exit;
                } else {
                    throw new Exception("Erreur lors de la mise à jour de la réservation");
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        
        // Récupérer les détails de la réservation
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   u.id as user_id, u.nom, u.prenom, u.email,
                   ps.id as place_id, ps.numero as place_numero, ps.type as place_type
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            JOIN parking_spaces ps ON r.place_id = ps.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $reservation = $stmt->fetch();
        
        if (!$reservation) {
            $_SESSION['error'] = "Réservation non trouvée";
            header('Location: ' . BASE_URL . '/?page=admin&action=reservations');
            exit;
        }
        
        // Liste des places pour le formulaire
        $stmt = $this->db->query("
            SELECT id, numero, type, status FROM parking_spaces 
            WHERE status = 'libre' OR id = " . $reservation['place_id'] . "
            ORDER BY numero
        ");
        $places = $stmt->fetchAll();
        
        require_once 'app/Views/admin/reservations/edit.php';
    }

    public function deleteReservation() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "Identifiant de réservation manquant";
            header('Location: ' . BASE_URL . '/?page=admin&action=reservations');
            exit;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Récupérer les infos de la réservation avant suppression (pour libérer la place)
            $stmt = $this->db->prepare("
                SELECT place_id, status, user_id FROM reservations WHERE id = ?
            ");
            $stmt->execute([$id]);
            $reservation = $stmt->fetch();
            
            if (!$reservation) {
                throw new Exception("Réservation non trouvée");
            }
            
            // 1. Récupérer les paiements associés à la réservation
            $stmt = $this->db->prepare("SELECT id FROM paiements WHERE reservation_id = ?");
            $stmt->execute([$id]);
            $paiements = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // 2. Supprimer d'abord les remboursements liés aux paiements
            if (!empty($paiements)) {
                $placeholders = implode(',', array_fill(0, count($paiements), '?'));
                $stmt = $this->db->prepare("DELETE FROM remboursements WHERE paiement_id IN ($placeholders)");
                $stmt->execute($paiements);
            }
            
            // 3. Maintenant supprimer les paiements
            $stmt = $this->db->prepare("DELETE FROM paiements WHERE reservation_id = ?");
            $stmt->execute([$id]);
            
            // 4. Enfin, supprimer la réservation
            $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = ?");
            $stmt->execute([$id]);
            
            // 5. Si la réservation était confirmée, libérer la place
            if ($reservation['status'] === 'confirmée') {
                $stmt = $this->db->prepare("UPDATE parking_spaces SET status = 'libre' WHERE id = ?");
                $stmt->execute([$reservation['place_id']]);
            }
            
            // Notifier l'utilisateur
            require_once 'app/Services/NotificationService.php';
            $notificationService = new NotificationService();
            $notificationService->createNotification(
                $reservation['user_id'],
                'Réservation supprimée',
                "Votre réservation a été supprimée par l'administration.",
                'reservation'
            );
            
            $this->db->commit();
            $_SESSION['success'] = "Réservation supprimée avec succès";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/?page=admin&action=reservations');
        exit;
    }
}
