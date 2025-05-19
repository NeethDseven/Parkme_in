<?php
require_once 'app/Services/NotificationService.php';

class UserController {
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }
        $this->db = Database::connect();
    }

    public function listReservations() {
        // Activer le débogage pour voir ce qui se passe
        error_log("Exécution de listReservations pour l'utilisateur ID: " . $_SESSION['user_id']);
        
        try {
            // S'assurer que l'utilisateur est connecté
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = "Vous devez être connecté pour accéder à vos réservations.";
                header('Location: ' . BASE_URL . '/?page=login');
                exit;
            }
            
            // TRÈS IMPORTANT : requête SQL corrigée pour récupérer les réservations de l'utilisateur
            $stmt = $this->db->prepare("
                SELECT r.*, 
                       ps.numero as place_numero, 
                       ps.type as place_type,
                       p.montant, 
                       p.status as payment_status
                FROM reservations r
                JOIN parking_spaces ps ON r.place_id = ps.id
                LEFT JOIN paiements p ON p.reservation_id = r.id
                WHERE r.user_id = ?
            ");
            
            error_log("Exécution de la requête SQL pour l'utilisateur ID: " . $_SESSION['user_id']);
            $stmt->execute([$_SESSION['user_id']]);
            
            // Récupérer les résultats
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Débogage pour voir les réservations récupérées
            error_log("Nombre de réservations trouvées: " . count($reservations));
            foreach ($reservations as $key => $res) {
                error_log("Réservation #$key - ID: {$res['id']}, Status: {$res['status']}, Place: {$res['place_numero']}");
            }
        } catch (Exception $e) {
            // En cas d'erreur, journaliser et initialiser un tableau vide
            error_log("ERREUR dans listReservations: " . $e->getMessage());
            $reservations = [];
            $_SESSION['error'] = "Une erreur est survenue lors de la récupération de vos réservations.";
        }
        
        // Afficher la vue avec les réservations
        require_once 'app/Views/user/reservations.php';
    }

    public function cancelReservation() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "Identifiant de réservation manquant";
            header('Location: ' . BASE_URL . '/?page=user&action=reservations');
            exit;
        }
        
        try {
            // Vérifier que l'utilisateur est bien propriétaire de cette réservation
            $stmt = $this->db->prepare("
                SELECT place_id, status FROM reservations 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $reservation = $stmt->fetch();
            
            if (!$reservation) {
                throw new Exception("Réservation non trouvée ou accès non autorisé");
            }
            
            $this->db->beginTransaction();
            
            // 1. Récupérer les paiements associés à la réservation
            $stmt = $this->db->prepare("SELECT id FROM paiements WHERE reservation_id = ?");
            $stmt->execute([$id]);
            $paiements = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // 2. Supprimer d'abord les remboursements liés aux paiements (s'il y en a)
            if (!empty($paiements)) {
                $placeholders = implode(',', array_fill(0, count($paiements), '?'));
                $stmt = $this->db->prepare("DELETE FROM remboursements WHERE paiement_id IN ($placeholders)");
                $stmt->execute($paiements);
            }
            
            // Option 1: Si on veut vraiment supprimer le paiement
            $stmt = $this->db->prepare("DELETE FROM paiements WHERE reservation_id = ?");
            $stmt->execute([$id]);
            
            // Option 2 (alternative): Marquer le paiement comme annulé plutôt que de le supprimer
            /*
            if (!empty($paiements)) {
                $stmt = $this->db->prepare("UPDATE paiements SET status = 'annule' WHERE reservation_id = ?");
                $stmt->execute([$id]);
            }
            */
            
            // Mettre à jour le statut de la réservation à "annulée"
            $stmt = $this->db->prepare("UPDATE reservations SET status = 'annulée' WHERE id = ?");
            $stmt->execute([$id]);
            
            // Si la réservation était "confirmée", libérer la place
            if ($reservation['status'] === 'confirmée') {
                $stmt = $this->db->prepare("UPDATE parking_spaces SET status = 'libre' WHERE id = ?");
                $stmt->execute([$reservation['place_id']]);
            }
            
            $this->db->commit();
            $_SESSION['success'] = "Votre réservation a été annulée avec succès.";
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de l'annulation: " . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/?page=user&action=reservations');
        exit;
    }

    public function showPayment() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }
        
        try {
            $reservation_id = $_GET['reservation_id'] ?? null;
            if (!$reservation_id) {
                throw new Exception("Réservation non trouvée");
            }
            
            $stmt = $this->db->prepare("
                SELECT p.*, r.date_debut, r.date_fin, ps.numero, ps.type
                FROM paiements p
                JOIN reservations r ON p.reservation_id = r.id
                JOIN parking_spaces ps ON r.place_id = ps.id
                WHERE p.reservation_id = ? AND r.user_id = ?
            ");
            $stmt->execute([$reservation_id, $_SESSION['user_id']]);
            $paiement = $stmt->fetch();
            
            if (!$paiement) {
                throw new Exception("Paiement non trouvé");
            }
            
            require_once 'app/Views/user/payment.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/?page=user&action=reservations');
            exit;
        }
    }

    public function processPayment() {
        require_once 'app/Services/PaymentValidator.php';
        require_once 'app/Services/EmailService.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new PaymentValidator();
            $error = $validator->validateCard(
                $_POST['card_number'],
                $_POST['card_expiry'],
                $_POST['card_cvv']
            );
            
            if ($error) {
                $_SESSION['payment_error'] = $error;
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $paiement_id = $_POST['paiement_id'];
            
            try {
                // Récupérer les détails du paiement
                $paiementDetails = $this->getReservationDetails($paiement_id);
                
                // Validation du paiement
                $stmt = $this->db->prepare("
                    UPDATE paiements 
                    SET status = 'valide' 
                    WHERE id = ? AND 
                          reservation_id IN (SELECT id FROM reservations WHERE user_id = ?)
                ");
                
                if ($stmt->execute([$paiement_id, $_SESSION['user_id']])) {
                    // Envoi de l'email de confirmation
                    $emailService = new EmailService();
                    $emailService->sendPaymentConfirmation(
                        $_SESSION['user_email'],
                        $paiementDetails
                    );
                    
                    // Ajouter une notification de paiement réussi
                    require_once 'app/Services/NotificationService.php';
                    $notificationService = new NotificationService();
                    
                    // Utiliser le service de notification avec les données JSON pour un meilleur affichage
                    $notificationService->createPaymentNotification(
                        $_SESSION['user_id'],
                        $paiementDetails['reservation_id'],
                        $paiementDetails['montant']
                    );
                    
                    $_SESSION['success'] = 'Paiement confirmé. Un email de confirmation vous a été envoyé.';
                    header('Location: ' . BASE_URL . '/?page=user&action=reservations');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['payment_error'] = $e->getMessage();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }
    }

    private function getReservationDetails($paiement_id) {
        $stmt = $this->db->prepare("
            SELECT p.*, r.date_debut, r.date_fin, ps.numero
            FROM paiements p
            JOIN reservations r ON p.reservation_id = r.id
            JOIN parking_spaces ps ON r.place_id = ps.id
            WHERE p.id = ?
        ");
        $stmt->execute([$paiement_id]);
        return $stmt->fetch();
    }

    public function downloadInvoice() {
        require_once 'app/Services/PDFService.php';
        
        $paiement_id = $_GET['paiement_id'] ?? null;
        $pdfService = new PDFService();
        $filepath = $pdfService->generateInvoice($paiement_id);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        readfile(ROOT_PATH . '/' . $filepath);
        exit;
    }

    public function downloadReceipt() {
        require_once 'app/Services/PDFService.php';
        
        $id = $_GET['id'] ?? null;
        if (!$id || !isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Réservation non trouvée";
            header('Location: ' . BASE_URL . '/?page=user&action=reservations');
            exit;
        }
        
        // Vérifier que la réservation appartient à l'utilisateur
        $stmt = $this->db->prepare("
            SELECT r.*, ps.numero as place_numero, ps.type as place_type,
                   p.montant, p.status as payment_status
            FROM reservations r
            JOIN parking_spaces ps ON r.place_id = ps.id
            LEFT JOIN paiements p ON p.reservation_id = r.id
            WHERE r.id = ? AND r.user_id = ? AND r.status = 'confirmée'
        ");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $reservation = $stmt->fetch();
        
        if (!$reservation) {
            $_SESSION['error'] = "Réservation non trouvée ou non confirmée";
            header('Location: ' . BASE_URL . '/?page=user&action=reservations');
            exit;
        }
        
        $pdfService = new PDFService();
        $filePath = $pdfService->generateReservationReceipt($reservation);
        
        // Téléchargement du PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="reservation_' . $id . '.pdf"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function requestRefund() {
        $paiement_id = $_POST['paiement_id'] ?? null;
        $raison = $_POST['raison'] ?? '';
        
        $stmt = $this->db->prepare("
            INSERT INTO remboursements (paiement_id, montant, raison)
            SELECT id, montant, ? FROM paiements WHERE id = ? AND status = 'valide'
        ");
        
        if ($stmt->execute([$raison, $paiement_id])) {
            $_SESSION['success'] = 'Demande de remboursement enregistrée';
        }
        
        header('Location: ' . BASE_URL . '/?page=user&action=reservations');
        exit;
    }

    public function paymentHistory() {
        $stmt = $this->db->prepare("
            SELECT p.*, r.date_debut, r.date_fin, ps.numero as place_numero,
                   f.numero_facture, rem.status as remboursement_status
            FROM paiements p
            JOIN reservations r ON p.reservation_id = r.id
            JOIN parking_spaces ps ON r.place_id = ps.id
            LEFT JOIN factures f ON p.id = f.paiement_id
            LEFT JOIN remboursements rem ON p.id = rem.paiement_id
            WHERE r.user_id = ?
            ORDER BY p.date_paiement DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $historique = $stmt->fetchAll();
        
        require_once 'app/Views/user/payment_history.php';
    }

    public function dashboard() {
        $stats = [
            'reservations_actives' => $this->getActiveReservations(),
            'total_depense' => $this->getTotalSpent(),
            'prochaine_reservation' => $this->getNextReservation()
        ];
        require_once 'app/Views/user/dashboard.php';
    }

    private function getActiveReservations() {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE user_id = ? AND status = 'confirmee' 
            AND date_fin > NOW()
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchColumn();
    }

    private function getTotalSpent() {
        $stmt = $this->db->prepare("
            SELECT SUM(p.montant) 
            FROM paiements p
            JOIN reservations r ON p.reservation_id = r.id
            WHERE r.user_id = ? AND p.status = 'valide'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchColumn() ?? 0;
    }

    private function getNextReservation() {
        $stmt = $this->db->prepare("
            SELECT r.*, p.numero as place_numero
            FROM reservations r
            JOIN parking_spaces p ON r.place_id = p.id
            WHERE r.user_id = ? AND r.status = 'confirmee' 
            AND r.date_debut > NOW()
            ORDER BY r.date_debut ASC
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }

    /**
     * Affiche les notifications de l'utilisateur
     */
    public function notifications() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $notificationService = new NotificationService();
        $notifications = $notificationService->getUserNotifications($user_id);

        require_once 'app/Views/user/notifications.php';
    }

    /**
     * Marque une notification spécifique comme lue
     */
    public function mark_read() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        if (isset($_GET['id'])) {
            $notification_id = (int)$_GET['id'];
            $user_id = $_SESSION['user_id'];
            
            $notificationService = new NotificationService();
            $notificationService->markAsRead($notification_id, $user_id);
        }
        
        header('Location: index.php?page=user&action=notifications');
        exit;
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function mark_all_read() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $notificationService = new NotificationService();
        $notificationService->markAllAsRead($user_id);
        
        header('Location: index.php?page=user&action=notifications');
        exit;
    }

    public function markNotificationRead() {
        if (isset($_GET['id'])) {
            require_once 'app/Services/NotificationService.php';
            $notificationService = new NotificationService();
            
            // Ajouter l'ID de l'utilisateur comme second paramètre
            $notificationService->markAsRead($_GET['id'], $_SESSION['user_id']);
        }
        
        header('Location: ' . BASE_URL . '/?page=user&action=notifications');
        exit;
    }

    /**
     * Affiche le profil de l'utilisateur et permet sa mise à jour
     */
    public function profile() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        $errors = [];
        $success = false;
        
        // Récupérer les informations de l'utilisateur
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        // Traitement du formulaire de mise à jour du profil
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $notifications_active = isset($_POST['notifications_active']) ? 1 : 0;
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validation des données
            if (empty($nom)) $errors[] = "Le nom est requis";
            if (empty($prenom)) $errors[] = "Le prénom est requis";
            if (empty($email)) $errors[] = "L'email est requis";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format d'email invalide";
            
            // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Cet email est déjà utilisé";
            }
            
            // Mise à jour du mot de passe si demandé
            if (!empty($new_password)) {
                if (strlen($new_password) < 8) {
                    $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
                } elseif ($new_password !== $confirm_password) {
                    $errors[] = "Les mots de passe ne correspondent pas";
                } else {
                    // Vérifier le mot de passe actuel
                    $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $current_hash = $stmt->fetchColumn();
                    
                    if (!password_verify($current_password, $current_hash)) {
                        $errors[] = "Le mot de passe actuel est incorrect";
                    }
                }
            }
            
            // Si aucune erreur, mettre à jour le profil
            if (empty($errors)) {
                try {
                    $this->db->beginTransaction();
                    
                    // Mise à jour des informations de base
                    $sql = "UPDATE users SET nom = ?, prenom = ?, email = ?, telephone = ?, 
                            notifications_active = ? WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$nom, $prenom, $email, $telephone, $notifications_active, $user_id]);
                    
                    // Mise à jour du mot de passe si nécessaire
                    if (!empty($new_password)) {
                        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$password_hash, $user_id]);
                    }
                    
                    $this->db->commit();
                    $success = true;
                    
                    // Mettre à jour les données de session
                    $_SESSION['user_email'] = $email;
                    
                    // Récupérer les informations mises à jour
                    $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                    
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $errors[] = "Une erreur est survenue : " . $e->getMessage();
                }
            }
        }
        
        require_once 'app/Views/user/profile.php';
    }
}
