<?php
require_once 'core/BaseController.php';
require_once 'app/models/Reservation.php';
require_once 'app/models/ParkingSpot.php';
require_once 'app/models/Database.php';
require_once 'app/models/Parking.php';

class ReservationController extends BaseController {
    
    public function __construct() {
        // Remplacer session_start() par une vérification
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }
    
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // Récupérer les réservations de l'utilisateur
        $reservations = Reservation::findByUserId($userId);
        
        // Classer les réservations par statut
        $activeReservations = [];
        $pendingReservations = [];
        $pastReservations = [];
        $cancelledReservations = [];
        
        foreach ($reservations as $reservation) {
            // S'assurer que $reservation est un tableau et possède la clé 'statut'
            $statut = isset($reservation['statut']) ? strtolower((string)$reservation['statut']) : '';
            $dateFin = isset($reservation['date_fin']) ? (string)$reservation['date_fin'] : '';
            
            // Utiliser les valeurs par défaut si les clés n'existent pas
            if ($statut === 'confirmée' && strtotime($dateFin) > time()) {
                $activeReservations[] = $reservation;
            } elseif ($statut === 'en_attente') {
                $pendingReservations[] = $reservation;
            } elseif ($statut === 'annulée') {
                $cancelledReservations[] = $reservation;
            } else {
                $pastReservations[] = $reservation;
            }
        }
        
        $this->render('reservation/index', [
            'activeReservations' => $activeReservations,
            'pendingReservations' => $pendingReservations,
            'pastReservations' => $pastReservations,
            'cancelledReservations' => $cancelledReservations
        ]);
    }
    
    /**
     * Afficher les détails d'une réservation
     */
    public function view() {
        $userId = $_SESSION['user_id'];
        $reservationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Récupérer les détails de la réservation
        $reservation = $this->getReservationDetails($reservationId, $userId);
        
        if (!$reservation) {
            // Rediriger vers la liste des réservations si la réservation n'existe pas
            $this->redirect('reservation', 'index', ['error' => 'Réservation introuvable']);
            return;
        }
        
        $this->render('reservation/view', ['reservation' => $reservation]);
    }
    
    /**
     * Créer une nouvelle réservation
     */
    public function create() {
        $userId = $_SESSION['user_id'];
        $errors = [];
        $parkingId = isset($_GET['parking_id']) ? (int)$_GET['parking_id'] : null;
        
        // Étape 1: Sélection d'un parking
        if (!$parkingId) {
            $parkings = Parking::getAll();
            
            $this->render('reservation/create', [
                'parkings' => $parkings
            ]);
            return;
        }
        
        // Étape 2: Sélection d'une date et heure
        $parkingDetails = Parking::getById($parkingId);
        
        // Vérifier si le parking existe
        if (!$parkingDetails) {
            $this->redirect('reservation', 'create', ['error' => 'Parking introuvable']);
            return;
        }
        
        // Vérifier si les dates ont été sélectionnées
        $startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : null;
        $endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : null;
        
        // Si les dates sont sélectionnées, rechercher les places disponibles
        $availableSpots = [];
        if ($startDate && $endDate) {
            error_log("Dates sélectionnées: début={$startDate}, fin={$endDate}");
            
            // Convertir les dates pour validation
            $startDateTime = strtotime($startDate);
            $endDateTime = strtotime($endDate);
            $currentTime = time();
            
            // Vérification de sécurité supplémentaire pour les dates
            if (!$startDateTime || !$endDateTime) {
                error_log("Erreur: Conversion de date échouée");
                $errors[] = "Format de date invalide. Veuillez réessayer.";
            }
            // Vérifier que les dates sont valides
            elseif ($startDateTime <= $currentTime) {
                error_log("Erreur: Date de début dans le passé");
                $errors[] = "La date de début doit être dans le futur (au minimum 15 minutes après l'heure actuelle).";
            }
            elseif ($startDateTime >= $endDateTime) {
                error_log("Erreur: Date de fin avant date de début");
                $errors[] = "La date de fin doit être après la date de début.";
            }
            elseif (($endDateTime - $startDateTime) < 1800) { // 30 minutes en secondes
                error_log("Erreur: Durée trop courte");
                $errors[] = "La durée de réservation doit être d'au moins 30 minutes.";
            }
            
            if (empty($errors)) {
                // Formater les dates pour la base de données
                $startDateFormatted = date('Y-m-d H:i:s', $startDateTime);
                $endDateFormatted = date('Y-m-d H:i:s', $endDateTime);
                
                error_log("Dates formatées: début={$startDateFormatted}, fin={$endDateFormatted}");
                
                // Chercher les places disponibles pour cette période
                $availableSpots = ParkingSpot::getAvailableSpotsByPeriod($parkingId, $startDateFormatted, $endDateFormatted);
                
                error_log("Places disponibles trouvées: " . count($availableSpots));
                
                // Déboguer - vérifier les places individuelles
                if (empty($availableSpots)) {
                    error_log("Aucune place disponible trouvée pour le parking {$parkingId}. Vérification des places individuelles...");
                    
                    // Récupérer toutes les places puis les tester une par une
                    $allSpots = ParkingSpot::getAllByParkingId($parkingId);
                    foreach ($allSpots as $spot) {
                        $isAvailable = ParkingSpot::isSpotAvailableForPeriod($spot['id'], $startDateFormatted, $endDateFormatted);
                        error_log("Test place {$spot['id']}: " . ($isAvailable ? "DISPONIBLE" : "NON DISPONIBLE"));
                    }
                }
            }
        }
        
        // Traitement du formulaire de réservation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("POST request received for reservation creation");
            
            $spotId = isset($_POST['spot_id']) ? (int)$_POST['spot_id'] : 0;
            $startDate = isset($_POST['start_date']) ? trim($_POST['start_date']) : null;
            $endDate = isset($_POST['end_date']) ? trim($_POST['end_date']) : null;
            $vehicle = isset($_POST['vehicle']) ? trim($_POST['vehicle']) : null;
            $parkingId = isset($_POST['parking_id']) ? (int)$_POST['parking_id'] : 0;
            
            error_log("Form data: spotId=$spotId, startDate=$startDate, endDate=$endDate, parkingId=$parkingId");
            
            // Validation des données
            if (!$spotId) {
                $errors[] = "Veuillez sélectionner une place de parking.";
            }
            
            if (!$startDate || !$endDate) {
                $errors[] = "Les dates de début et de fin sont requises.";
            }
            
            // Convertir les dates pour validation et formatage
            $startDateTime = strtotime($startDate);
            $endDateTime = strtotime($endDate);
            
            if (!$startDateTime || !$endDateTime) {
                $errors[] = "Les dates fournies sont invalides.";
            }
            
            if (empty($errors)) {
                error_log("No validation errors, proceeding to create reservation");
                
                // Formater les dates pour la base de données
                $startDateFormatted = date('Y-m-d H:i:s', $startDateTime);
                $endDateFormatted = date('Y-m-d H:i:s', $endDateTime);
                
                error_log("Formatted dates: start=$startDateFormatted, end=$endDateFormatted");
                
                // Récupérer les détails du parking pour calculer le prix
                require_once 'app/models/Parking.php';
                $parking = Parking::getById($parkingId);
                
                if (!$parking) {
                    $errors[] = "Parking introuvable.";
                } else {
                    // Calculer le prix de la réservation
                    $hourlyRate = (float)$parking['tarif_horaire'];
                    $hours = ($endDateTime - $startDateTime) / 3600;
                    $price = $hours * $hourlyRate;
                    
                    error_log("Price calculation: $hours hours * $hourlyRate €/hour = $price €");
                    
                    // Vérifier que la place est toujours disponible
                    if (ParkingSpot::isSpotAvailableForPeriod($spotId, $startDateFormatted, $endDateFormatted)) {
                        error_log("Spot $spotId is available for the period");
                        
                        // Créer la réservation
                        $reservationId = Reservation::create($userId, $spotId, $startDateFormatted, $endDateFormatted, $price, $vehicle);
                        
                        if ($reservationId) {
                            error_log("Reservation created successfully with ID: $reservationId");
                            
                            // Mettre à jour le statut de la place (optionnel car fait dans Reservation::create)
                            ParkingSpot::updateStatus($spotId, 'occupee');
                            
                            // Rediriger vers la page de paiement
                            $this->redirect('payment', 'checkout', ['reservation_id' => $reservationId]);
                            return;
                        } else {
                            error_log("Failed to create reservation");
                            $errors[] = "Erreur lors de la création de la réservation. Veuillez réessayer.";
                        }
                    } else {
                        error_log("Spot $spotId is no longer available for the period");
                        $errors[] = "Cette place n'est plus disponible pour la période sélectionnée. Veuillez choisir une autre place ou une autre période.";
                    }
                }
            }
        }
        
        // Afficher le formulaire de réservation
        $this->render('reservation/create', [
            'parkingId' => $parkingId,
            'parkingDetails' => $parkingDetails,
            'parkingSpots' => isset($parkingSpots) ? $parkingSpots : [],
            'availableSpots' => $availableSpots,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'errors' => $errors,
            'formData' => $_POST
        ]);
    }
    
    /**
     * Annuler une réservation
     */
    public function cancel() {
        $userId = $_SESSION['user_id'];
        $reservationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Récupérer les détails de la réservation
        $reservation = $this->getReservationDetails($reservationId, $userId);
        
        if (!$reservation) {
            $this->redirect('reservation', 'index', ['error' => 'Réservation introuvable']);
            return;
        }
        
        // Vérifier si la réservation peut être annulée
        $canCancel = true;
        $message = '';
        
        // Vérifier que la réservation n'est pas déjà terminée ou annulée
        $statut = isset($reservation['statut']) ? (string)$reservation['statut'] : '';
        if ($statut === 'terminée') {
            $canCancel = false;
            $message = "Vous ne pouvez pas annuler une réservation terminée.";
        } elseif ($statut === 'annulée') {
            $canCancel = false;
            $message = "Cette réservation est déjà annulée.";
        }
        
        // Vérifier le délai d'annulation
        $dateFin = isset($reservation['date_fin']) ? (string)$reservation['date_fin'] : '';
        if ($canCancel && strtotime($dateFin) < time()) {
            $canCancel = false;
            $message = "Vous ne pouvez pas annuler une réservation qui est déjà passée.";
        }
        
        // Annuler la réservation si possible
        if ($canCancel) {
            if (Reservation::cancel($reservationId)) {
                // Créer une notification pour l'utilisateur
                require_once 'app/models/Notification.php';
                Notification::create($userId, "Votre réservation #" . $reservationId . " a été annulée avec succès.", "annulation");
                
                $this->redirect('reservation', 'index', ['success' => 'Réservation annulée avec succès']);
                return;
            } else {
                $message = "Erreur lors de l'annulation de la réservation.";
            }
        }
        
        // Rediriger vers la page des réservations avec un message d'erreur
        $this->redirect('reservation', 'index', ['error' => $message]);
    }
    
    /**
     * Afficher l'historique des réservations
     */
    public function history() {
        $userId = $_SESSION['user_id'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10; // Nombre d'éléments par page
        
        // Utiliser getHistory au lieu de countHistoryByUserId qui n'existe pas
        $result = Reservation::getHistory($userId, $page, $perPage);
        
        $this->render('reservation/history', [
            'reservations' => $result['reservations'],
            'pagination' => $result['pagination']
        ]);
    }
    
    /**
     * Imprimer une réservation
     */
    public function print() {
        $userId = $_SESSION['user_id'];
        $reservationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Récupérer les détails de la réservation
        $reservation = $this->getReservationDetails($reservationId, $userId);
        
        if (!$reservation) {
            $this->redirect('reservation', 'index', ['error' => 'Réservation introuvable']);
            return;
        }
        
        // Rendre la vue d'impression sans le layout standard
        $this->render('reservation/print', ['reservation' => $reservation]);
    }
    
    /**
     * Récupère les détails d'une réservation
     *
     * @param int $reservationId ID de la réservation
     * @param int $userId ID de l'utilisateur
     * @return array|null Détails de la réservation ou null si non trouvée
     */
    private function getReservationDetails($reservationId, $userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Modifier cette requête pour utiliser des jointures et récupérer toutes les informations nécessaires
            $stmt = $conn->prepare("
                SELECT r.*, pp.numero as numero_place, pk.nom as parking_nom, pk.adresse as parking_adresse, pk.tarif_horaire
                FROM reservations r
                JOIN places_parking pp ON r.emplacement_id = pp.id
                JOIN parkings pk ON pp.parking_id = pk.id
                WHERE r.id = :id AND r.utilisateur_id = :utilisateur_id
            ");
            
            $stmt->bindParam(':id', $reservationId, PDO::PARAM_INT);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des détails de la réservation: " . $e->getMessage());
            return null;
        }
    }
}
?>
