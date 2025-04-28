<?php
require_once __DIR__ . '/../../core/Model.php';

class Reservation extends Model {
    // Explicitly declare all properties to avoid deprecation warnings
    public $id;
    public $utilisateur_id;
    public $emplacement_id;
    public $date_debut;
    public $date_fin;
    public $date_reservation;
    public $vehicule;
    public $statut;
    public $prix;
    public $code_acces;
    public $numero_place;
    
    // Add any other properties that might be used
    private $db;

    protected static $table = 'reservations';
    protected static $fillable = [
        'utilisateur_id', 'emplacement_id', 'date_debut', 'date_fin',
        'date_reservation', 'vehicule', 'statut', 'prix', 'code_acces'
    ];
    
    // Generate a random access code
    public static function generateAccessCode() {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }
    
    // Calculate price based on duration and spot rate
    public static function calculatePrice($startTime, $endTime, $hourlyRate) {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        $hours = $end->diff($start)->h + ($end->diff($start)->days * 24);
        return $hours * $hourlyRate;
    }
    
    // Confirm reservation
    public function confirm() {
        $this->statut = 'confirmée';
        
        // Generate access code if not already set
        if (empty($this->code_acces)) {
            $this->code_acces = self::generateAccessCode();
        }
        
        // Update the spot status
        $spot = ParkingSpot::findById($this->emplacement_id);
        if ($spot) {
            $spot->markAsOccupied();
        }
        
        return $this->save();
    }
    
    /**
     * Mark a reservation as cancelled
     *
     * @return bool Success or failure
     */
    public function cancel() {
        $this->statut = 'annulée';
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("UPDATE reservations SET statut = :statut WHERE id = :id");
            $stmt->bindParam(':statut', $this->statut);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erreur lors de l'annulation de la réservation: " . $e->getMessage());
            return false;
        }
    }
    
    // Get user who made the reservation
    public function getUser() {
        return User::findById($this->utilisateur_id);
    }
    
    // Get parking spot
    public function getParkingSpot() {
        return ParkingSpot::findById($this->emplacement_id);
    }
    
    // Get payment information
    public function getPayment() {
        $db = Database::getInstance();
        $query = "SELECT * FROM paiements WHERE reservation_id = :reservation_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':reservation_id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find all reservations by user ID
     *
     * @param int $userId User ID to find reservations for
     * @return array Array of Reservation objects
     */
    public static function findByUserId($userId) {
        // Use the correct method to get a database connection
        $conn = Database::getInstance()->getConnection();
        
        // Use 'utilisateur_id' instead of 'user_id' to match the database schema
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE utilisateur_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $reservations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reservation = new Reservation();
            foreach ($row as $key => $value) {
                $reservation->$key = $value;
            }
            $reservations[] = $reservation;
        }
        
        return $reservations;
    }
    
    /**
     * Find historical reservations by user ID
     * 
     * @param int $userId The user ID to find reservations for
     * @param int $limit Number of items per page
     * @param int $offset Starting position
     * @return array An array of Reservation objects
     */
    public static function findHistoryByUserId($userId, $limit = null, $offset = null) {
        $db = Database::getInstance()->getConnection();
        
        $query = "SELECT r.*, ps.numero as numero_place 
                  FROM reservations r
                  LEFT JOIN emplacements ps ON r.emplacement_id = ps.id
                  WHERE r.utilisateur_id = :userId AND 
                  (r.statut LIKE '%annul%' OR r.date_fin < NOW())
                  ORDER BY r.date_debut DESC";
        
        // Ajouter la pagination si demandée
        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        
        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        
        $reservations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reservation = new Reservation();
            foreach ($row as $key => $value) {
                $reservation->$key = $value;
            }
            $reservations[] = $reservation;
        }
        
        return $reservations;
    }
    
    /**
     * Compte le nombre de réservations historiques pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de réservations
     */
    public static function countHistoryByUserId($userId) {
        $db = Database::getInstance()->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM reservations 
                  WHERE utilisateur_id = :userId AND 
                  (statut LIKE '%annul%' OR date_fin < NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Create a Reservation instance from an array of data
     * 
     * @param array $data Reservation data
     * @return Reservation
     */
    protected static function createFromArray($data) {
        $reservation = new static();
        foreach ($data as $key => $value) {
            $reservation->$key = $value;
        }
        return $reservation;
    }
    
    /**
     * Create a new reservation
     * 
     * @param array $data Reservation data
     * @return int|false ID of the new reservation or false on failure
     */
    public static function create($data) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO reservations 
                (utilisateur_id, emplacement_id, date_debut, date_fin, date_reservation,
                vehicule, statut, prix, code_acces)
                VALUES
                (:utilisateur_id, :emplacement_id, :date_debut, :date_fin, NOW(),
                :vehicule, :statut, :prix, :code_acces)
            ");
            
            $stmt->bindParam(':utilisateur_id', $data['utilisateur_id']);
            $stmt->bindParam(':emplacement_id', $data['emplacement_id']);
            $stmt->bindParam(':date_debut', $data['date_debut']);
            $stmt->bindParam(':date_fin', $data['date_fin']);
            $stmt->bindParam(':vehicule', $data['vehicule']);
            $stmt->bindParam(':statut', $data['statut']);
            $stmt->bindParam(':prix', $data['prix']);
            $stmt->bindParam(':code_acces', $data['code_acces']);
            
            if ($stmt->execute()) {
                return $conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creating reservation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get full reservation details including parking and spot information
     * 
     * @param int $id Reservation ID
     * @return array|false Full reservation details or false if not found
     */
    public static function getFullReservationDetails($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                r.*,
                u.nom as utilisateur_nom,
                u.prenom as utilisateur_prenom,
                u.email as utilisateur_email,
                pp.numero as place_numero,
                pp.numero_place as place_numero_place,
                pp.etage as place_etage,
                pp.type as place_type,
                p.id as parking_id,
                p.nom as parking_nom,
                p.adresse as parking_adresse,
                p.code_postal as parking_code_postal,
                p.ville as parking_ville,
                p.ouverture as parking_ouverture,
                p.fermeture as parking_fermeture,
                p.tarif_horaire as parking_tarif_horaire
            FROM 
                reservations r
            JOIN 
                utilisateurs u ON r.utilisateur_id = u.id
            JOIN 
                places_parking pp ON r.emplacement_id = pp.id
            JOIN 
                parkings p ON pp.parking_id = p.id
            WHERE 
                r.id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update reservation status
     *
     * @param int $id Reservation ID
     * @param string $status New status
     * @return bool Success or failure
     */
    public function updateStatus($id, $status)
    {
        $id = (int)$id;
        
        try {
            $query = "UPDATE reservations SET statut = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status, $id]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get reservation by ID
     *
     * @param int $id Reservation ID
     * @return object|null Reservation object or null if not found
     */
    public function getById($id)
    {
        $id = (int)$id;
        
        // Utiliser getInstance() pour obtenir une connexion à la base de données
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $reservation = $stmt->fetch(PDO::FETCH_OBJ);
            return $reservation ? $reservation : null;
        } catch (Exception $e) {
            error_log("Error in getById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find a reservation by its ID
     *
     * @param int $id Reservation ID
     * @return Reservation|null The reservation object or null if not found
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        
        $reservation = new Reservation();
        foreach ($data as $key => $value) {
            $reservation->$key = $value;
        }
        
        return $reservation;
    }
}
?>
