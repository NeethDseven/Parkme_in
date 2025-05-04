<?php
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/ParkingSpot.php';

class Reservation extends Model {
    // Propriétés de la classe
    public $id;
    public $utilisateur_id;
    public $emplacement_id;
    public $date_debut;
    public $date_fin;
    public $date_reservation;
    public $statut;
    public $prix;
    public $code_acces;
    public $vehicule;
    
    protected static $table = 'reservations';
    
    /**
     * Créer une nouvelle réservation
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $spotId ID de la place de parking
     * @param string $startDate Date et heure de début (format Y-m-d H:i:s)
     * @param string $endDate Date et heure de fin (format Y-m-d H:i:s)
     * @param float $price Prix total de la réservation
     * @param string $vehicle Description du véhicule (optionnel)
     * @return int|bool ID de la réservation créée ou false en cas d'échec
     */
    public static function create($userId, $spotId, $startDate, $endDate, $price, $vehicle = null) {
        // Logs détaillés pour le debugging
        error_log("Attempt to create reservation: userId=$userId, spotId=$spotId, startDate=$startDate, endDate=$endDate, price=$price");
        
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Transaction pour assurer l'intégrité des données
            $conn->beginTransaction();
            
            // Vérifier que la place existe
            $spotCheck = $conn->prepare("SELECT id, statut FROM places_parking WHERE id = :id");
            $spotCheck->bindParam(':id', $spotId, PDO::PARAM_INT);
            $spotCheck->execute();
            $spot = $spotCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$spot) {
                error_log("Error: Spot with id $spotId does not exist");
                $conn->rollBack();
                return false;
            }
            
            // Vérifier que la place est disponible pour cette période
            $availabilityCheck = $conn->prepare("
                SELECT COUNT(*) as count FROM reservations 
                WHERE emplacement_id = :spotId 
                AND statut IN ('en_attente', 'confirmée')
                AND (date_debut < :endDate AND date_fin > :startDate)
            ");
            $availabilityCheck->bindParam(':spotId', $spotId, PDO::PARAM_INT);
            $availabilityCheck->bindParam(':startDate', $startDate);
            $availabilityCheck->bindParam(':endDate', $endDate);
            $availabilityCheck->execute();
            
            if ($availabilityCheck->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                error_log("Error: Spot $spotId is already booked for this period");
                $conn->rollBack();
                return false;
            }
            
            // Générer un code d'accès aléatoire
            $accessCode = self::generateAccessCode();
            
            // Créer la réservation
            $stmt = $conn->prepare("
                INSERT INTO reservations (utilisateur_id, emplacement_id, date_debut, date_fin, statut, prix, code_acces, vehicule)
                VALUES (:utilisateur_id, :emplacement_id, :date_debut, :date_fin, :statut, :prix, :code_acces, :vehicule)
            ");
            
            $statut = 'en_attente';
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':emplacement_id', $spotId, PDO::PARAM_INT);
            $stmt->bindParam(':date_debut', $startDate);
            $stmt->bindParam(':date_fin', $endDate);
            $stmt->bindParam(':statut', $statut);
            $stmt->bindParam(':prix', $price);
            $stmt->bindParam(':code_acces', $accessCode);
            $stmt->bindParam(':vehicule', $vehicle);
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error: Failed to execute reservation creation SQL");
                $conn->rollBack();
                return false;
            }
            
            $reservationId = $conn->lastInsertId();
            
            if (!$reservationId) {
                error_log("Error: Could not get last insert ID for reservation");
                $conn->rollBack();
                return false;
            }
            
            // Créer une notification pour l'utilisateur
            $notificationStmt = $conn->prepare("
                INSERT INTO notifications (utilisateur_id, message, type, lu)
                VALUES (:utilisateur_id, :message, :type, 0)
            ");
            
            $message = "Votre réservation #" . $reservationId . " a été créée avec succès. Veuillez procéder au paiement pour la confirmer.";
            $type = "reservation";
            
            $notificationStmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $notificationStmt->bindParam(':message', $message);
            $notificationStmt->bindParam(':type', $type);
            
            $notificationResult = $notificationStmt->execute();
            
            if (!$notificationResult) {
                error_log("Warning: Failed to create notification for reservation #$reservationId");
                // Continue anyway, not critical
            }
            
            $conn->commit();
            error_log("Success: Reservation #$reservationId created successfully");
            
            return $reservationId;
            
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Database error in Reservation::create: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("General error in Reservation::create: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer une réservation par son ID
     * 
     * @param int $id ID de la réservation
     * @return object|null Objet Reservation ou null si non trouvée
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }
            
            $reservation = new self();
            foreach ($data as $key => $value) {
                $reservation->$key = $value;
            }
            
            return $reservation;
        } catch (PDOException $e) {
            error_log("Error finding reservation: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer les réservations d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $status Statut des réservations à récupérer (optionnel)
     * @return array Liste des réservations
     */
    public static function findByUserId($userId, $status = null) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $query = "
                SELECT r.*, pp.numero as numero_place, p.nom as parking_nom
                FROM reservations r
                JOIN places_parking pp ON r.emplacement_id = pp.id
                JOIN parkings p ON pp.parking_id = p.id
                WHERE r.utilisateur_id = :utilisateur_id
            ";
            
            if ($status !== null) {
                $query .= " AND r.statut = :statut";
            }
            
            $query .= " ORDER BY r.date_debut DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            
            if ($status !== null) {
                $stmt->bindParam(':statut', $status);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding reservations by user: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les réservations actives et futures d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des réservations
     */
    public static function getCurrentAndUpcoming($userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT r.*, pp.numero as numero_place, p.nom as parking_nom
                FROM reservations r
                JOIN places_parking pp ON r.emplacement_id = pp.id
                JOIN parkings p ON pp.parking_id = p.id
                WHERE r.utilisateur_id = :utilisateur_id
                  AND r.statut = 'confirmée'
                  AND r.date_fin > NOW()
                ORDER BY r.date_debut ASC
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding current and upcoming reservations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer l'historique des réservations d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $page Numéro de la page
     * @param int $perPage Nombre d'éléments par page
     * @return array Tableau contenant les réservations et les informations de pagination
     */
    public static function getHistory($userId, $page = 1, $perPage = 10) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Calculer l'offset pour la pagination
            $offset = ($page - 1) * $perPage;
            
            // Compter le nombre total de réservations pour la pagination
            $countStmt = $conn->prepare("
                SELECT COUNT(*) as total
                FROM reservations
                WHERE utilisateur_id = :utilisateur_id
                  AND (statut = 'terminée' OR statut = 'annulée' OR date_fin < NOW())
            ");
            
            $countStmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Récupérer les réservations
            $stmt = $conn->prepare("
                SELECT r.*, pp.numero as numero_place, p.nom as parking_nom
                FROM reservations r
                LEFT JOIN places_parking pp ON r.emplacement_id = pp.id
                LEFT JOIN parkings p ON pp.parking_id = p.id
                WHERE r.utilisateur_id = :utilisateur_id
                  AND (r.statut = 'terminée' OR r.statut = 'annulée' OR r.date_fin < NOW())
                ORDER BY r.date_debut DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculer les informations de pagination
            $totalPages = ceil($total / $perPage);
            $pagination = [
                'total' => $total,
                'perPage' => $perPage,
                'page' => $page,
                'totalPages' => $totalPages,
                'hasPrevPage' => $page > 1,
                'hasNextPage' => $page < $totalPages
            ];
            
            return [
                'reservations' => $reservations,
                'pagination' => $pagination
            ];
        } catch (PDOException $e) {
            error_log("Error getting reservation history: " . $e->getMessage());
            return [
                'reservations' => [],
                'pagination' => [
                    'total' => 0,
                    'perPage' => $perPage,
                    'page' => $page,
                    'totalPages' => 0,
                    'hasPrevPage' => false,
                    'hasNextPage' => false
                ]
            ];
        }
    }
    
    /**
     * Vérifier les disponibilités d'une place de parking
     * 
     * @param int $spotId ID de la place de parking
     * @param string $startDate Date et heure de début (format Y-m-d H:i:s)
     * @param string $endDate Date et heure de fin (format Y-m-d H:i:s)
     * @return bool True si la place est disponible, false sinon
     */
    public static function checkAvailability($spotId, $startDate, $endDate) {
        // Utiliser la méthode de ParkingSpot pour vérifier la disponibilité
        return ParkingSpot::isSpotAvailableForPeriod($spotId, $startDate, $endDate);
    }
    
    /**
     * Annuler une réservation
     * 
     * @param int $id ID de la réservation
     * @return bool Succès ou échec
     */
    public static function cancel($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Récupérer les informations de la réservation
            $reservation = self::findById($id);
            
            if (!$reservation) {
                return false;
            }
            
            // Mettre à jour le statut de la réservation
            $stmt = $conn->prepare("UPDATE reservations SET statut = 'annulée' WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Libérer la place de parking
                $spotStmt = $conn->prepare("UPDATE places_parking SET statut = 'libre' WHERE id = :id");
                $spotStmt->bindParam(':id', $reservation->emplacement_id, PDO::PARAM_INT);
                $spotStmt->execute();
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error cancelling reservation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour le statut d'une réservation
     * 
     * @param int $id ID de la réservation
     * @param string $status Nouveau statut
     * @return bool Succès ou échec
     */
    public static function updateStatus($id, $status) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("UPDATE reservations SET statut = :statut WHERE id = :id");
            $stmt->bindParam(':statut', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating reservation status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculer le prix d'une réservation
     * 
     * @param int $parkingId ID du parking
     * @param string $startDate Date et heure de début (format Y-m-d H:i:s)
     * @param string $endDate Date et heure de fin (format Y-m-d H:i:s)
     * @return float Prix calculé
     */
    public static function calculatePrice($parkingId, $startDate, $endDate) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Récupérer le tarif horaire du parking
            $stmt = $conn->prepare("SELECT tarif_horaire FROM parkings WHERE id = :id");
            $stmt->bindParam(':id', $parkingId, PDO::PARAM_INT);
            $stmt->execute();
            
            $parking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$parking) {
                return 0;
            }
            
            // Calculer la différence d'heures
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            $interval = $start->diff($end);
            
            // Convertir l'intervalle en heures
            $hours = $interval->h + ($interval->days * 24);
            $minutes = $interval->i;
            
            // Arrondir au quart d'heure supérieur
            if ($minutes > 0) {
                $hours++;
            }
            
            // Calculer le prix
            $price = $hours * $parking['tarif_horaire'];
            
            return $price;
        } catch (PDOException $e) {
            error_log("Error calculating price: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Générer un code d'accès aléatoire
     * 
     * @return string Code d'accès
     */
    private static function generateAccessCode() {
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    }
}
?>
