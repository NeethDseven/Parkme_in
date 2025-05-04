<?php
require_once 'core/BaseController.php';
require_once 'app/models/Payment.php';
require_once 'app/models/Reservation.php';
require_once 'app/models/User.php';
require_once 'app/models/Notification.php';
require_once 'app/services/EmailService.php';

class PaymentController extends BaseController {
    
    public function __construct() {
        // Vérifier si l'utilisateur est connecté
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }
    
    /**
     * Affiche le formulaire de paiement pour une réservation
     */
    public function checkout() {
        // Vérifier si une réservation est spécifiée
        if (!isset($_GET['reservation_id']) || !is_numeric($_GET['reservation_id'])) {
            $this->redirect('reservation', 'index');
        }
        
        $reservationId = (int)$_GET['reservation_id'];
        $userId = $_SESSION['user_id'];
        
        // Récupérer les détails de la réservation
        $reservation = $this->getReservationDetails($reservationId, $userId);
        
        if (!$reservation) {
            $this->redirect('reservation', 'index');
        }
        
        // Vérifier si la réservation est déjà payée
        $payment = Payment::findByReservationId($reservationId);
        
        if ($payment && $payment['statut'] === 'complete') {
            $this->redirect('reservation', 'view', ['id' => $reservationId, 'message' => 'Cette réservation a déjà été payée.']);
        }
        
        // Afficher la vue de paiement
        $this->render('payment/checkout', [
            'reservation' => $reservation,
            'payment' => $payment
        ]);
    }
    
    /**
     * Traite le paiement d'une réservation
     */
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reservation', 'index');
        }
        
        // Vérifier les données du formulaire
        $reservationId = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;
        $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
        $cardNumber = isset($_POST['card_number']) ? $_POST['card_number'] : '';
        $cardExpiry = isset($_POST['card_expiry']) ? $_POST['card_expiry'] : '';
        $cardCvc = isset($_POST['card_cvc']) ? $_POST['card_cvc'] : '';
        
        $userId = $_SESSION['user_id'];
        
        // Récupérer les détails de la réservation
        $reservation = $this->getReservationDetails($reservationId, $userId);
        
        if (!$reservation) {
            $this->redirect('reservation', 'index');
        }
        
        // Validation de base
        $errors = [];
        
        if (empty($paymentMethod)) {
            $errors[] = "Veuillez sélectionner une méthode de paiement.";
        }
        
        if ($paymentMethod === 'carte') {
            if (empty($cardNumber) || !preg_match('/^\d{16}$/', $cardNumber)) {
                $errors[] = "Numéro de carte invalide. Veuillez entrer les 16 chiffres sans espaces.";
            }
            
            if (empty($cardExpiry) || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $cardExpiry)) {
                $errors[] = "Date d'expiration invalide. Format attendu: MM/YY";
            }
            
            if (empty($cardCvc) || !preg_match('/^\d{3,4}$/', $cardCvc)) {
                $errors[] = "Code CVC invalide.";
            }
        }
        
        if (!empty($errors)) {
            $this->render('payment/checkout', [
                'reservation' => $reservation,
                'errors' => $errors
            ]);
            return;
        }
        
        // Simuler le traitement du paiement (dans un environnement réel, vous utiliseriez une passerelle de paiement)
        $amount = $reservation->prix;
        $transactionId = 'TRX' . time() . rand(1000, 9999);
        
        // Créer ou mettre à jour le paiement
        $payment = Payment::findByReservationId($reservationId);
        
        if ($payment) {
            Payment::updateStatus($payment['id'], 'complete', $transactionId);
            $paymentId = $payment['id'];
        } else {
            $paymentId = Payment::create($reservationId, $userId, $amount, $paymentMethod, 'complete', $transactionId);
        }
        
        if ($paymentId) {
            // Mettre à jour le statut de la réservation
            Reservation::updateStatus($reservationId, 'confirmée');
            
            // Créer une notification
            $message = "Votre paiement de " . number_format($amount, 2) . " € pour la réservation #" . 
                      $reservationId . " a été confirmé. Votre code d'accès est: " . $reservation->code_acces;
            Notification::create($userId, $message, 'paiement');
            
            // Envoyer un email de confirmation
            $user = User::findById($userId);
            
            if ($user) {
                $userName = $user['prenom'] . ' ' . $user['nom'];
                EmailService::sendReservationConfirmation($user['email'], $userName, $reservation);
            }
            
            // Rediriger vers la page de succès
            $this->redirect('payment', 'success', ['id' => $paymentId]);
        } else {
            // En cas d'échec du paiement
            $this->render('payment/checkout', [
                'reservation' => $reservation,
                'errors' => ["Une erreur est survenue lors du traitement du paiement. Veuillez réessayer."]
            ]);
        }
    }
    
    /**
     * Affiche la page de succès du paiement
     */
    public function success() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $this->redirect('reservation', 'index');
        }
        
        $paymentId = (int)$_GET['id'];
        $userId = $_SESSION['user_id'];
        
        // Récupérer les détails du paiement
        $payment = Payment::findById($paymentId);
        
        if (!$payment || $payment['utilisateur_id'] != $userId) {
            $this->redirect('reservation', 'index');
        }
        
        // Récupérer les détails de la réservation
        $reservation = $this->getReservationDetails($payment['reservation_id'], $userId);
        
        $this->render('payment/success', [
            'payment' => $payment,
            'reservation' => $reservation
        ]);
    }
    
    /**
     * Affiche l'historique des paiements de l'utilisateur
     */
    public function history() {
        $userId = $_SESSION['user_id'];
        
        // Récupérer tous les paiements de l'utilisateur
        $payments = Payment::findByUserId($userId);
        
        $this->render('payment/history', [
            'payments' => $payments
        ]);
    }
    
    /**
     * Récupère les détails d'une réservation
     *
     * @param int $reservationId ID de la réservation
     * @param int $userId ID de l'utilisateur
     * @return object|null Détails de la réservation ou null si non trouvée
     */
    private function getReservationDetails($reservationId, $userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT r.*, p.numero as numero_place, pk.nom as parking_nom
                FROM reservations r
                JOIN places_parking p ON r.emplacement_id = p.id
                JOIN parkings pk ON p.parking_id = pk.id
                WHERE r.id = :id AND r.utilisateur_id = :utilisateur_id
            ");
            
            $stmt->bindParam(':id', $reservationId, PDO::PARAM_INT);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $reservation = $stmt->fetch(PDO::FETCH_OBJ);
            
            return $reservation ?: null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des détails de la réservation: " . $e->getMessage());
            return null;
        }
    }
}
