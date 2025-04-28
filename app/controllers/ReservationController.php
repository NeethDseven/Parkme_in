<?php

require_once 'app/models/Reservation.php';

class ReservationController extends BaseController {
    
    // Vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
    private function checkUserLoggedIn() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        return $_SESSION['user_id'];
    }
    
    // Affiche les réservations actives et toutes les réservations
    public function index() {
        require_once 'app/models/ParkingSpot.php';
        $userId = $this->checkUserLoggedIn();
        $reservations = Reservation::findByUserId($userId);
        
        $activeReservations = [];
        // Toutes les réservations, peu importe leur statut
        $allReservations = [];
        
        foreach ($reservations as $reservation) {
            // Copie toutes les réservations dans la liste complète
            $allReservations[] = $reservation;
            
            // Vérifier si la réservation est annulée
            $isCancelled = (strtolower($reservation->statut) === 'annulée' || 
                           strtolower($reservation->statut) === 'annulee' || 
                           strtolower($reservation->statut) === 'annule' ||
                           strtolower($reservation->statut) === 'canceled' ||
                           strtolower($reservation->statut) === 'cancelled');
            
            // Vérifier si la réservation est déjà terminée
            $currentTime = time();
            $endTime = strtotime($reservation->date_fin);
            $isFinished = $endTime < $currentTime;
            
            // Une réservation est active si elle n'est PAS annulée et PAS terminée
            if (!$isCancelled && !$isFinished) {
                $activeReservations[] = $reservation;
            }
        }
        
        $this->render('reservation/index', [
            'activeReservations' => $activeReservations,
            'allReservations' => $allReservations,
            'success' => isset($_GET['success']) ? $_GET['success'] : null
        ]);
    }
    
    // Affiche l'historique des réservations avec pagination
    public function history() {
        $userId = $this->checkUserLoggedIn();
        
        // Paramètres de pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $itemsPerPage = 10;
        $offset = ($page - 1) * $itemsPerPage;
        
        // Récupération des données de pagination
        $totalReservations = Reservation::countHistoryByUserId($userId);
        $totalPages = ceil($totalReservations / $itemsPerPage);
        
        // Ajustement de la page courante
        $page = max(1, min($page, $totalPages > 0 ? $totalPages : 1));
        
        // Récupération des réservations avec pagination
        $reservationsObjects = Reservation::findHistoryByUserId($userId, $itemsPerPage, $offset);
        require_once 'app/models/ParkingSpot.php';
        
        // Préparation des données pour la vue
        $reservations = [];
        foreach ($reservationsObjects as $res) {
            $parkingSpot = ParkingSpot::findById($res->emplacement_id);
            $reservations[] = [
                'id' => $res->id,
                'utilisateur_id' => $res->utilisateur_id,
                'emplacement_id' => $res->emplacement_id,
                'numero_place' => $parkingSpot ? $parkingSpot->numero : 'N/A',
                'date_debut' => $res->date_debut,
                'date_fin' => $res->date_fin,
                'vehicule' => $res->vehicule,
                'statut' => $res->statut,
                'prix' => $res->prix,
                'code_acces' => $res->code_acces
            ];
        }
        
        $this->render('reservation/history', [
            'reservations' => $reservations,
            'pagination' => [
                'page' => $page,
                'totalPages' => $totalPages,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalReservations,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1
            ],
            'success' => isset($_GET['success']) ? $_GET['success'] : null
        ]);
    }
    
    // Crée une nouvelle réservation
    public function create() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login&redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        $userId = $_SESSION['user_id'];
        $parkingId = isset($_GET['parking_id']) ? (int)$_GET['parking_id'] : null;
        
        // Chargement des parkings
        require_once 'app/models/Parking.php';
        $parkings = [];
        $parkingDetails = null;
        $parkingSpots = [];
        
        if (!$parkingId) {
            $parkings = Parking::getAll();
        } else {
            require_once 'app/models/ParkingSpot.php';
            $parkingDetails = Parking::getById($parkingId);
            $parkingSpots = ParkingSpot::getAvailableByParkingId($parkingId);
        }
        
        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération des données du formulaire
            $spotId = isset($_POST['spot_id']) ? (int)$_POST['spot_id'] : null;
            $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : null;
            $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : null;
            $vehicle = isset($_POST['vehicle']) ? $_POST['vehicle'] : '';
            $errors = [];
            
            // Validation des données
            if (!$spotId) $errors[] = "Veuillez sélectionner une place de parking.";
            if (!$startDate) $errors[] = "Veuillez sélectionner une date et heure de début.";
            if (!$endDate) $errors[] = "Veuillez sélectionner une date et heure de fin.";
            
            if ($startDate && $endDate) {
                $start = new DateTime($startDate);
                $end = new DateTime($endDate);
                if ($start >= $end) $errors[] = "La date de fin doit être postérieure à la date de début.";
            }
            
            // Création de la réservation si pas d'erreurs
            if (empty($errors)) {
                $parkingInfo = Parking::getParkingInfoForSpot($spotId);
                $hourlyRate = $parkingInfo['tarif_horaire'];
                
                // Calcul du prix
                $start = new DateTime($startDate);
                $end = new DateTime($endDate);
                $duration = $start->diff($end);
                $hours = $duration->h + ($duration->days * 24);
                $price = $hourlyRate * $hours;
                
                // Préparation des données de réservation
                $reservationData = [
                    'utilisateur_id' => $userId,
                    'emplacement_id' => $spotId,
                    'date_debut' => $startDate,
                    'date_fin' => $endDate,
                    'vehicule' => $vehicle,
                    'statut' => 'confirmée',
                    'prix' => $price,
                    'code_acces' => strtoupper(substr(md5(uniqid()), 0, 8))
                ];
                
                // Création de la réservation
                $reservationId = Reservation::create($reservationData);
                if ($reservationId) {
                    ParkingSpot::updateStatus($spotId, 'occupée');
                    header('Location: /projet/Parkme_in-master/index.php?controller=reservation&action=confirmation&id=' . $reservationId);
                    exit;
                } else {
                    $errors[] = "Erreur lors de la création de la réservation. Veuillez réessayer.";
                }
            }
            
            $this->render('reservation/create', [
                'errors' => $errors,
                'parkings' => $parkings,
                'parkingId' => $parkingId,
                'parkingDetails' => $parkingDetails ?? null,
                'parkingSpots' => $parkingSpots ?? [],
                'formData' => $_POST
            ]);
        } else {
            $this->render('reservation/create', [
                'parkings' => $parkings,
                'parkingId' => $parkingId,
                'parkingDetails' => $parkingDetails ?? null,
                'parkingSpots' => $parkingSpots ?? []
            ]);
        }
    }

    // Affiche la confirmation d'une réservation
    public function confirmation($id = null) {
        $userId = $this->checkUserLoggedIn();
        
        if (!$id) {
            $this->setFlash('error', 'ID de réservation non fourni.');
            $this->redirect('reservation');
            return;
        }
        
        // Récupération de la réservation
        $reservationModel = new Reservation();
        $reservation = $reservationModel->getById($id);
        
        if (!$reservation || $reservation->utilisateur_id != $userId) {
            $this->setFlash('error', !$reservation ? 'Réservation introuvable.' : 'Vous n\'êtes pas autorisé à voir cette réservation.');
            $this->redirect('reservation');
            return;
        }
        
        // Affichage de la confirmation
        $this->render('reservation/confirmation', [
            'reservation' => $reservation,
            'homeUrl' => '/projet/Parkme_in-master/index.php',
            'reservationsUrl' => '/projet/Parkme_in-master/index.php?controller=reservation&action=index',
            'isLoggedIn' => true,
            'userData' => [
                'id' => $userId,
                'nom' => $_SESSION['user_name'] ?? '',
                'role' => $_SESSION['user_role'] ?? 'user'
            ]
        ]);
    }
    
    // Annule une réservation
    public function cancel() {
        $userId = $this->checkUserLoggedIn();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$id) {
            header('Location: index.php?controller=reservation&action=index&error=1');
            exit;
        }
        
        // Récupération de la réservation
        require_once 'app/models/ParkingSpot.php';
        $reservation = Reservation::findById($id);
        
        if (!$reservation || $reservation->utilisateur_id != $userId) {
            header('Location: index.php?controller=reservation&action=index&error=' . (!$reservation ? '1' : 'unauthorized'));
            exit;
        }
        
        // Annulation de la réservation
        if ($reservation->cancel()) {
            $parkingSpot = ParkingSpot::findById($reservation->emplacement_id);
            if ($parkingSpot) $parkingSpot->markAsFree();
            header('Location: /projet/Parkme_in-master/index.php?controller=reservation&action=history&success=cancel');
        } else {
            header('Location: /projet/Parkme_in-master/index.php?controller=reservation&action=index&error=1');
        }
        exit;
    }

    // Imprime une réservation
    public function print() {
        $userId = $this->checkUserLoggedIn();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$id) {
            $this->setFlash('error', 'ID de réservation non fourni.');
            $this->redirect('reservation');
            return;
        }
        
        // Récupération de la réservation
        $reservationModel = new Reservation();
        $reservation = $reservationModel->getById($id);
        
        if (!$reservation || $reservation->utilisateur_id != $userId) {
            $this->setFlash('error', !$reservation ? 'Réservation introuvable.' : 'Vous n\'êtes pas autorisé à voir cette réservation.');
            $this->redirect('reservation');
            return;
        }
        
        // Affichage de l'impression
        $this->render('reservation/print', [
            'reservation' => $reservation,
            'isLoggedIn' => true,
            'userName' => $_SESSION['user_name'] ?? ''
        ]);
    }
}
?>
