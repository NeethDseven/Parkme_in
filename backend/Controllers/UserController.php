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
                SELECT r.*, r.place_id, r.status as reservation_status, p.id as paiement_id, p.montant, ps.numero as place_numero
                FROM reservations r
                LEFT JOIN paiements p ON p.reservation_id = r.id
                JOIN parking_spaces ps ON r.place_id = ps.id
                WHERE r.id = ? AND r.user_id = ? AND (r.status = 'confirmée' OR r.status = 'en_attente')
            ");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $reservation = $stmt->fetch();
            
            if (!$reservation) {
                throw new Exception("Réservation non trouvée ou déjà annulée");
            }
            
            // Stocker les informations du créneau avant l'annulation pour les alertes
            $placeId = $reservation['place_id'];
            $dateDebut = $reservation['date_debut'];
            $dateFin = $reservation['date_fin'];
            
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
            
            // Si la réservation était confirmée et avait un paiement, créer un remboursement
            if ($reservation['reservation_status'] === 'confirmée' && $reservation['paiement_id']) {
                // Mettre à jour le statut du paiement
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
                
                $messageNotif = "Votre réservation de la place n°{$reservation['place_numero']} a été annulée avec succès. Une demande de remboursement a été initiée.";
            } else {
                // Si la réservation était en attente, simplement mettre à jour le statut du paiement si existant
                if ($reservation['paiement_id']) {
                    $stmt = $this->db->prepare("UPDATE paiements SET status = 'annule' WHERE id = ?");
                    $stmt->execute([$reservation['paiement_id']]);
                }
                
                $messageNotif = "Votre réservation de la place n°{$reservation['place_numero']} a été annulée avec succès.";
            }
            
            // Ajouter une notification d'annulation
            require_once 'backend/Services/NotificationService.php';
            $notificationService = new NotificationService();
            $notificationService->createNotification(
                $_SESSION['user_id'],
                'Réservation annulée',
                $messageNotif,
                'annulation'
            );
            
            // Vérifier et notifier les utilisateurs qui ont des alertes sur ce créneau
            require_once 'backend/Services/AlerteDisponibiliteService.php';
            require_once 'backend/Services/LoggerService.php';
            $logger = new LoggerService();
            $alerteService = new AlerteDisponibiliteService();
            
            // Journaliser l'annulation
            $logger->info("Annulation de réservation", [
                'reservationId' => $id,
                'placeId' => $placeId,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin
            ]);
            
            // Vérifier les alertes pour cette place libérée
            $alertesSent = $alerteService->checkAlertsForCancellation($placeId, $dateDebut, $dateFin);
            
            $this->db->commit();
            
            if ($reservation['reservation_status'] === 'confirmée') {
                $message = "Réservation annulée avec succès.";
                if ($reservation['paiement_id']) {
                    $message .= " Une demande de remboursement a été créée.";
                }
            } else {
                $message = "Réservation annulée avec succès.";
            }
            
            if ($alertesSent > 0) {
                $message .= " " . $alertesSent . " utilisateur(s) ont été notifiés de la disponibilité de ce créneau.";
            }
            
            $_SESSION['success'] = $message;
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
                $this->db->beginTransaction();
                
                // Récupérer les détails du paiement et de la réservation
                $stmt = $this->db->prepare("
                    SELECT p.*, r.id as reservation_id, r.place_id, ps.numero as place_numero
                    FROM paiements p
                    JOIN reservations r ON p.reservation_id = r.id
                    JOIN parking_spaces ps ON r.place_id = ps.id
                    WHERE p.id = ? AND 
                          r.user_id = ?
                ");
                $stmt->execute([$paiement_id, $_SESSION['user_id']]);
                $paiementDetails = $stmt->fetch();
                
                if (!$paiementDetails) {
                    throw new Exception("Données de paiement invalides");
                }
                
                // Valider le paiement
                $stmt = $this->db->prepare("
                    UPDATE paiements 
                    SET status = 'valide' 
                    WHERE id = ?
                ");
                
                if (!$stmt->execute([$paiement_id])) {
                    throw new Exception("Erreur lors de la validation du paiement");
                }
                
                // Mettre à jour la réservation en tant que confirmée
                $stmt = $this->db->prepare("
                    UPDATE reservations 
                    SET status = 'confirmée' 
                    WHERE id = ?
                ");
                
                if (!$stmt->execute([$paiementDetails['reservation_id']])) {
                    throw new Exception("Erreur lors de la confirmation de la réservation");
                }
                
                // Mettre à jour le statut de la place si la réservation commence maintenant
                $stmt = $this->db->prepare("
                    UPDATE parking_spaces
                    SET status = 'occupe'
                    WHERE id = ? AND
                    (SELECT date_debut FROM reservations WHERE id = ?) <= NOW()
                ");
                
                $stmt->execute([$paiementDetails['place_id'], $paiementDetails['reservation_id']]);
                
                // Ajouter une notification de confirmation
                require_once 'backend/Services/NotificationService.php';
                $notificationService = new NotificationService();
                
                $notificationService->createNotification(
                    $_SESSION['user_id'],
                    'Réservation confirmée',
                    "Votre réservation de la place n°{$paiementDetails['place_numero']} a été confirmée suite à votre paiement.",
                    'paiement'
                );
                
                // Envoyer l'email de confirmation
                $emailService = new EmailService();
                $emailService->sendPaymentConfirmation(
                    $_SESSION['user_email'],
                    $paiementDetails
                );
                
                $this->db->commit();
                $_SESSION['success'] = 'Paiement confirmé. Un email de confirmation vous a été envoyé.';
                header('Location: ' . BASE_URL . '/?page=user&action=reservations');
                exit;
                
            } catch (Exception $e) {
                $this->db->rollBack();
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
                   f.numero_facture, rem.status as remboursement_status, 
                   rem.raison, rem.commentaire_admin, rem.date_demande as date_remboursement
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
        // Création d'un tableau de statistiques formaté pour JavaScript
        $stats = [
            'reservations_actives' => $this->getActiveReservations(),
            'total_depense' => $this->getTotalSpent(),
            'prochaine_reservation' => $this->getNextReservation()
        ];
        
        // Récupérer les données des réservations pour le graphique
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(date_debut, '%d/%m') as jour,
                COUNT(*) as nombre
            FROM reservations 
            WHERE user_id = ? 
            AND date_debut >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY jour
            ORDER BY date_debut
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $reservationsGraph = $stmt->fetchAll();
        
        // Récupérer les 5 dernières réservations
        $stmt = $this->db->prepare("
            SELECT r.*, ps.numero as place_numero, ps.type as place_type
            FROM reservations r
            JOIN parking_spaces ps ON r.place_id = ps.id
            WHERE r.user_id = ?
            ORDER BY r.date_debut DESC
            LIMIT 5
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $recentReservations = $stmt->fetchAll();
        
        // Préparer les données pour JavaScript dans un format unifié
        $jsData = [
            'hasCharts' => true,
            'occupationData' => $reservationsGraph,
            'recentReservations' => $recentReservations
        ];

        // Ajouter les scripts JS nécessaires
        $extraJS = ['dashboard-stats.js'];
        
        require_once 'frontend/Views/user/dashboard.php';
    }

    /**
     * Récupère le nombre de réservations actives
     * @return int Nombre de réservations actives
     */
    private function getActiveReservations() {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM reservations 
            WHERE user_id = ? 
            AND status = 'confirmée'
            AND date_fin > NOW()
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchColumn();
    }

    /**
     * Calcule le montant total dépensé par l'utilisateur
     * @return float Montant total dépensé en euros
     */
    private function getTotalSpent() {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(p.montant), 0) 
            FROM paiements p
            JOIN reservations r ON p.reservation_id = r.id
            WHERE r.user_id = ?
            AND p.status = 'valide'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return floatval($stmt->fetchColumn());
    }

    /**
     * Récupère la prochaine réservation de l'utilisateur
     * @return array|null Détails de la prochaine réservation ou null si aucune
     */
    private function getNextReservation() {
        $stmt = $this->db->prepare("
            SELECT r.*, ps.numero as place_numero, ps.type as place_type
            FROM reservations r
            JOIN parking_spaces ps ON r.place_id = ps.id
            WHERE r.user_id = ? 
            AND r.status = 'confirmée'
            AND r.date_debut > NOW()
            ORDER BY r.date_debut ASC
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }

    /**
     * Gère les préférences utilisateur avec support AJAX
     */
    public function savePreferences() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $defaultPayment = $_POST['default_payment'] ?? 'carte';
        $saveCardInfo = isset($_POST['save_card_info']) ? 1 : 0;
        $notificationsActive = isset($_POST['notifications_active']) ? 1 : 0;
        
        try {
            // Format des préférences en JSON pour plus de flexibilité
            $preferences = json_encode([
                'payment' => [
                    'default_method' => $defaultPayment,
                    'save_card_info' => $saveCardInfo
                ],
                'notifications' => [
                    'active' => $notificationsActive
                ]
            ]);
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET payment_preferences = ?, notifications_active = ? 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$preferences, $notificationsActive, $userId])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit;
    }

    /**
     * Vérifie les nouvelles notifications (endpoint AJAX)
     */
    public function check_notifications() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Non autorisé']);
            exit;
        }
        
        require_once 'backend/Services/NotificationService.php';
        $notificationService = new NotificationService();
        
        $unreadCount = $notificationService->getUnreadCount($_SESSION['user_id']);
        
        // Récupérer les nouvelles notifications depuis la dernière vérification
        $lastCheck = $_SESSION['last_notification_check'] ?? date('Y-m-d H:i:s', strtotime('-1 hour'));
        $_SESSION['last_notification_check'] = date('Y-m-d H:i:s');
        
        $newNotifications = $notificationService->getNewNotifications($_SESSION['user_id'], $lastCheck);
        
        echo json_encode([
            'unread_count' => $unreadCount,
            'new_notifications' => $newNotifications
        ]);
        
        exit;
    }

    /**
     * Affiche toutes les notifications de l'utilisateur
     */
    public function notifications() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }
        
        require_once 'backend/Services/NotificationService.php';
        $notificationService = new NotificationService();
        
        // Récupérer toutes les notifications de l'utilisateur
        $notifications = $notificationService->getUserNotifications($_SESSION['user_id']);
        
        // Compter les notifications non lues
        $unreadCount = $notificationService->getUnreadCount($_SESSION['user_id']);
        
        require_once 'frontend/Views/user/notifications.php';
    }
    
    /**
     * Marque toutes les notifications de l'utilisateur comme lues
     */
    public function mark_all_read() {
        header('Content-Type: application/json');
        
        // Vérifier si c'est une requête AJAX
        $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        if (!isset($_SESSION['user_id'])) {
            if ($isAjaxRequest) {
                echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            } else {
                $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page";
                header('Location: ' . BASE_URL . '/?page=login');
            }
            exit;
        }
        
        require_once 'backend/Services/NotificationService.php';
        $notificationService = new NotificationService();
        
        $success = $notificationService->markAllAsRead($_SESSION['user_id']);
        
        if ($isAjaxRequest) {
            echo json_encode(['success' => $success, 'unread_count' => 0]);
        } else {
            // Pour les requêtes non-AJAX, rediriger vers la page des notifications
            $_SESSION['success'] = "Toutes les notifications ont été marquées comme lues";
            header('Location: ' . BASE_URL . '/?page=user&action=notifications');
        }
        exit;
    }

    /**
     * Marque une notification spécifique comme lue
     */
    public function markNotificationRead() {
        header('Content-Type: application/json');
        
        // Vérifier si c'est une requête AJAX
        $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        if (!isset($_SESSION['user_id'])) {
            if ($isAjaxRequest) {
                echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            } else {
                $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page";
                header('Location: ' . BASE_URL . '/?page=login');
            }
            exit;
        }
        
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$id) {
            if ($isAjaxRequest) {
                echo json_encode(['success' => false, 'message' => 'ID de notification invalide']);
            } else {
                $_SESSION['error'] = "ID de notification invalide";
                header('Location: ' . BASE_URL . '/?page=user&action=notifications');
            }
            exit;
        }
        
        require_once 'backend/Services/NotificationService.php';
        $notificationService = new NotificationService();
        
        $success = $notificationService->markAsRead($id, $_SESSION['user_id']);
        $unreadCount = $notificationService->getUnreadCount($_SESSION['user_id']);
        
        if ($isAjaxRequest) {
            echo json_encode(['success' => $success, 'unread_count' => $unreadCount]);
        } else {
            // Pour les requêtes non-AJAX, rediriger vers la page des notifications
            $_SESSION['success'] = "Notification marquée comme lue";
            header('Location: ' . BASE_URL . '/?page=user&action=notifications');
        }
        exit;
    }
    
    /**
     * Affiche le profil de l'utilisateur
     */
    public function profile() {
        try {
            // Récupérer les informations de l'utilisateur
            $user_id = $_SESSION['user_id'];
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception("Utilisateur non trouvé");
            }

            // Récupérer les préférences
            $preferencesJson = $user['payment_preferences'] ?? '{}';
            $preferences = json_decode($preferencesJson, true) ?: [];
            
            // Extraire les préférences de paiement
            $paymentPreferences = $preferences['payment'] ?? [
                'default_method' => 'carte',
                'save_card_info' => false
            ];
            
            // Extraire les préférences de notification
            $notificationPreferences = $preferences['notifications'] ?? [
                'reservation_start' => true,
                'reservation_end' => true,
                'reminder' => true,
                'payments' => true,
                'alerts' => true
            ];

            // Charger la vue avec les données
            require_once 'frontend/Views/user/profile.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/?page=user&action=dashboard');
            exit;
        }
    }

    /**
     * Met à jour le profil de l'utilisateur
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=user&action=profile');
            exit;
        }

        try {
            $user_id = $_SESSION['user_id'];
            $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);

            // Validation des données
            if (!$prenom || !$nom || !$email) {
                throw new Exception("Tous les champs obligatoires doivent être remplis");
            }

            // Vérifier si l'email existe déjà pour un autre utilisateur
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Cette adresse email est déjà utilisée par un autre compte");
            }

            // Mise à jour du profil
            $stmt = $this->db->prepare("
                UPDATE users 
                SET prenom = ?, nom = ?, email = ?, telephone = ? 
                WHERE id = ?
            ");
            
            if (!$stmt->execute([$prenom, $nom, $email, $telephone, $user_id])) {
                throw new Exception("Erreur lors de la mise à jour du profil");
            }

            // Mettre à jour l'email dans la session
            $_SESSION['user_email'] = $email;
            
            $_SESSION['success'] = "Profil mis à jour avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/?page=user&action=profile');
        exit;
    }

    /**
     * Met à jour le mot de passe de l'utilisateur
     */
    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=user&action=profile');
            exit;
        }

        try {
            $user_id = $_SESSION['user_id'];
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validation des données
            if (!$current_password || !$new_password || !$confirm_password) {
                throw new Exception("Tous les champs sont obligatoires");
            }

            if ($new_password !== $confirm_password) {
                throw new Exception("Les nouveaux mots de passe ne correspondent pas");
            }

            if (strlen($new_password) < 8) {
                throw new Exception("Le nouveau mot de passe doit contenir au moins 8 caractères");
            }

            // Vérifier le mot de passe actuel
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($current_password, $user['password'])) {
                throw new Exception("Le mot de passe actuel est incorrect");
            }

            // Hasher le nouveau mot de passe
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            if (!$stmt->execute([$hashed_password, $user_id])) {
                throw new Exception("Erreur lors de la mise à jour du mot de passe");
            }

            $_SESSION['success'] = "Mot de passe mis à jour avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/?page=user&action=profile');
        exit;
    }

    /**
     * Met à jour les préférences de notification de l'utilisateur
     */
    public function updateNotificationPreferences() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=user&action=profile');
            exit;
        }

        try {
            $userId = $_SESSION['user_id'];
            $notificationsActive = isset($_POST['notifications_active']) ? 1 : 0;
            $notificationPreferences = $_POST['notification_preferences'] ?? [];
            
            // S'assurer que toutes les préférences ont des valeurs par défaut
            $defaultPreferences = [
                'reservation_start' => isset($notificationPreferences['reservation_start']) ? 1 : 0,
                'reservation_end' => isset($notificationPreferences['reservation_end']) ? 1 : 0,
                'reminder' => isset($notificationPreferences['reminder']) ? 1 : 0,
                'payments' => isset($notificationPreferences['payments']) ? 1 : 0,
                'alerts' => isset($notificationPreferences['alerts']) ? 1 : 0
            ];
            
            // Récupérer les préférences de paiement existantes pour ne pas les écraser
            $stmt = $this->db->prepare("SELECT payment_preferences FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $paymentPreferencesJson = $user['payment_preferences'] ?? '{}';
            $existingPreferences = json_decode($paymentPreferencesJson, true) ?: [];
            
            // Fusionner les préférences existantes avec les nouvelles
            $preferences = [
                'payment' => $existingPreferences['payment'] ?? [
                    'default_method' => 'carte',
                    'save_card_info' => false
                ],
                'notifications' => $defaultPreferences
            ];
            
            // Sauvegarder en JSON
            $preferencesJson = json_encode($preferences);
            
            // Mettre à jour dans la base de données
            $stmt = $this->db->prepare("
                UPDATE users 
                SET notifications_active = ?, payment_preferences = ? 
                WHERE id = ?
            ");
            
            if (!$stmt->execute([$notificationsActive, $preferencesJson, $userId])) {
                throw new Exception("Erreur lors de la mise à jour des préférences de notification");
            }
            
            $_SESSION['success'] = "Préférences de notification mises à jour avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/?page=user&action=profile');
        exit;
    }
}
