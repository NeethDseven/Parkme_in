<?php
require_once 'backend/Services/NotificationService.php';

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
        $stmt = $this->db->prepare("
            SELECT r.*, p.numero as place_numero, p.type as place_type
            FROM reservations r
            JOIN parking_spaces p ON r.place_id = p.id
            WHERE r.user_id = ?
            ORDER BY r.date_debut DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $reservations = $stmt->fetchAll();

        require_once 'frontend/Views/user/reservations.php';
    }

    public function cancelReservation() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = "Réservation non trouvée";
            header('Location: ' . BASE_URL . '/?page=user&action=reservations');
            exit;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Vérifier que la réservation existe et appartient à l'utilisateur
            $stmt = $this->db->prepare("
                SELECT r.*, r.place_id, p.id as paiement_id, p.montant, ps.numero as place_numero
                FROM reservations r
                LEFT JOIN paiements p ON p.reservation_id = r.id
                JOIN parking_spaces ps ON r.place_id = ps.id
                WHERE r.id = ? AND r.user_id = ? AND r.status = 'confirmée'
            ");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $reservation = $stmt->fetch();
            
            if (!$reservation) {
                throw new Exception("Réservation non trouvée ou déjà annulée");
            }
            
            // Mettre à jour le statut de la réservation
            $stmt = $this->db->prepare("UPDATE reservations SET status = 'annulée' WHERE id = ?");
            if (!$stmt->execute([$id])) {
                throw new Exception("Erreur lors de l'annulation de la réservation");
            }
            
            // Vérifier s'il y a d'autres réservations actives pour cette place
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM reservations 
                WHERE place_id = ? 
                AND status = 'confirmée'
                AND date_debut <= NOW()
                AND date_fin > NOW()
                AND id != ?
            ");
            $stmt->execute([$reservation['place_id'], $id]);
            $hasOtherActiveReservations = $stmt->fetchColumn() > 0;
            
            // Libérer la place uniquement s'il n'y a pas d'autres réservations actives
            if (!$hasOtherActiveReservations) {
                $stmt = $this->db->prepare("UPDATE parking_spaces SET status = 'libre' WHERE id = ?");
                if (!$stmt->execute([$reservation['place_id']])) {
                    throw new Exception("Erreur lors de la mise à jour de la place");
                }
            }
            
            // Mettre à jour le statut du paiement
            if ($reservation['paiement_id']) {
                $stmt = $this->db->prepare("UPDATE paiements SET status = 'annule' WHERE id = ?");
                if (!$stmt->execute([$reservation['paiement_id']])) {
                    throw new Exception("Erreur lors de la mise à jour du paiement");
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO remboursements (paiement_id, montant, raison)
                    VALUES (?, ?, 'Annulation par l\'utilisateur')
                ");
                if (!$stmt->execute([$reservation['paiement_id'], $reservation['montant']])) {
                    throw new Exception("Erreur lors de la création de la demande de remboursement");
                }
            }
            
            // Ajouter une notification d'annulation
            require_once 'backend/Services/NotificationService.php';
            $notificationService = new NotificationService();
            $notificationService->createNotification(
                $_SESSION['user_id'],
                'Réservation annulée',
                "Votre réservation de la place n°{$reservation['place_numero']} a été annulée avec succès.",
                'annulation'
            );
            
            $this->db->commit();
            $_SESSION['success'] = "Réservation annulée avec succès";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = $e->getMessage();
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
            
            require_once 'frontend/Views/user/payment.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/?page=user&action=reservations');
            exit;
        }
    }

    public function processPayment() {
        require_once 'backend/Services/PaymentValidator.php';
        require_once 'backend/Services/EmailService.php';
        
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
                    require_once 'backend/Services/NotificationService.php';
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
        require_once 'backend/Services/PDFService.php';
        
        $paiement_id = $_GET['paiement_id'] ?? null;
        $pdfService = new PDFService();
        $filepath = $pdfService->generateInvoice($paiement_id);
        
        if ($filepath && file_exists(ROOT_PATH . '/' . $filepath)) {
            header('Content-Type: text/html');
            header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
            readfile(ROOT_PATH . '/' . $filepath);
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de la génération de la facture";
            header('Location: ' . BASE_URL . '/?page=user&action=history');
            exit;
        }
    }

    public function downloadReceipt() {
        require_once 'backend/Services/PDFService.php';
        
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
        
        // Si le fichier a été généré avec succès
        if ($filePath && file_exists($filePath)) {
            // Pour les fichiers HTML, on peut soit les télécharger soit les afficher dans le navigateur
            header('Content-Type: text/html');
            header('Content-Disposition: inline; filename="reservation_' . $id . '.html"');
            readfile($filePath);
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de la génération du reçu";
            header('Location: ' . BASE_URL . '/?page=user&action=reservations');
            exit;
        }
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
        
        require_once 'frontend/Views/user/payment_history.php';
    }

    public function dashboard() {
        $stats = [
            'reservations_actives' => $this->getActiveReservations(),
            'total_depense' => $this->getTotalSpent(),
            'prochaine_reservation' => $this->getNextReservation()
        ];
        require_once 'frontend/Views/user/dashboard.php';
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

        require_once 'frontend/Views/user/notifications.php';
    }

    public function markNotificationRead() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $notification_id = $_GET['id'];
        $notificationService = new NotificationService();
        $notificationService->markAsRead($notification_id, $_SESSION['user_id']);

        header('Location: ' . BASE_URL . '/?page=user&action=notifications');
        exit;
    }

    public function mark_all_read() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $notificationService = new NotificationService();
        $notificationService->markAllAsRead($_SESSION['user_id']);

        header('Location: ' . BASE_URL . '/?page=user&action=notifications');
        exit;
    }

    public function profile() {
        // Récupérer les informations de l'utilisateur
        $user_id = $_SESSION['user_id'];
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Récupérer les préférences de paiement
        require_once 'backend/Models/UserModel.php';
        $userPreferences = new UserPreferences($this->db);
        $paymentPreferences = $userPreferences->getPaymentPreferences($user_id);

        require_once 'frontend/Views/user/profile.php';
    }
}
