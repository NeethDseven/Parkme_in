<?php
require_once __DIR__ . '/../../core/Model.php';

/**
 * ParkingSpot Model
 * 
 * This class represents the ParkingSpot model to handle parking spot-related data and operations
 */
class ParkingSpot extends Model {
    // Properties
    public $id;
    public $parking_id;
    public $numero;
    public $etage;
    public $type;
    public $statut;
    public $numero_place;
    public $tarif_horaire; // Ajout de la propriété manquante qui causait les avertissements
    
    protected static $table = 'places_parking';
    
    private $db;

    /**
     * Constructor - initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all parking spots
     * 
     * @return array Array of parking spot data
     */
    public function getAllSpots()
    {
        $query = "SELECT * FROM parking_spots";
        return $this->db->query($query);
    }

    /**
     * Get parking spots by parking ID
     * 
     * @param int $parkingId Parking ID
     * @return array Array of parking spot data
     */
    public function getSpotsByParkingId($parkingId)
    {
        $query = "SELECT * FROM parking_spots WHERE parking_id = :parking_id";
        return $this->db->query($query, ['parking_id' => $parkingId]);
    }

    /**
     * Get parking spot by ID
     * 
     * @param int $id Parking spot ID
     * @return array|bool Parking spot data or false if not found
     */
    public function getSpotById($id)
    {
        // Use integer casting to avoid parameter binding issues
        $id = (int)$id;
        
        // Try to get from emplacements table first
        $query = "SELECT * FROM emplacements WHERE id = $id";
        $result = $this->db->query($query)->fetch(PDO::FETCH_ASSOC);
        
        // If not found, try the parking_spots table
        if (!$result) {
            $query = "SELECT * FROM parking_spots WHERE id = $id";
            $result = $this->db->query($query)->fetch(PDO::FETCH_ASSOC);
        }
        
        return $result;
    }

    /**
     * Check if a parking spot is available for a specific time period
     *
     * @param int $spotId The parking spot ID
     * @param string $startDate Start date and time
     * @param string $endDate End date and time
     * @return bool True if available, false if occupied
     */
    public function isAvailable($spotId, $startDate, $endDate)
    {
        $query = "SELECT COUNT(*) as count FROM reservations 
                 WHERE spot_id = :spot_id 
                 AND NOT (end_time <= :start_time OR start_time >= :end_time)";
                 
        $result = $this->db->query($query, [
            'spot_id' => $spotId,
            'start_time' => $startDate,
            'end_time' => $endDate
        ])->fetch();
        
        return $result['count'] == 0;
    }

    /**
     * Get available parking spots by parking ID
     * 
     * @param int $parkingId Parking ID
     * @param string $startDate Optional start date to check availability
     * @param string $endDate Optional end date to check availability
     * @return array Array of available parking spots
     */
    public static function getAvailableByParkingId($parkingId)
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM places_parking WHERE parking_id = :parking_id AND statut = 'disponible'");
        $stmt->bindParam(':parking_id', $parkingId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get available parking spots by parking ID with pagination
     * 
     * @param int $parkingId Parking ID
     * @param int $page Current page number (default 1)
     * @param int $itemsPerPage Number of items per page (default 10)
     * @param string $startDate Optional start date to check availability
     * @param string $endDate Optional end date to check availability
     * @return array Array containing 'spots', 'total', 'totalPages', 'currentPage'
     */
    public static function getAvailableByParkingIdPaginated($parkingId, $page = 1, $itemsPerPage = 10, $startDate = null, $endDate = null)
    {
        $db = Database::getInstance();
        
        // Convert params to integers for security
        $parkingId = (int)$parkingId;
        $page = (int)$page;
        $itemsPerPage = (int)$itemsPerPage;
        
        // Ensure page is at least 1
        if ($page < 1) {
            $page = 1;
        }
        
        // Calculate offset for pagination
        $offset = ($page - 1) * $itemsPerPage;
        
        try {
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total FROM emplacements WHERE parking_id = $parkingId";
            $countResult = $db->query($countQuery)->fetch(PDO::FETCH_ASSOC);
            $total = $countResult['total'];
            
            // Calculate total pages
            $totalPages = ceil($total / $itemsPerPage);
            
            // Query with pagination
            $query = "SELECT * FROM emplacements WHERE parking_id = $parkingId LIMIT $itemsPerPage OFFSET $offset";
            $statement = $db->query($query);
            $spots = $statement->fetchAll(PDO::FETCH_ASSOC);
            
            // If dates are provided, filter for availability
            if ($startDate !== null && $endDate !== null) {
                // This would filter the results based on availability
                // Would need to implement actual availability logic
            }
            
            // Return data with pagination information
            return [
                'spots' => $spots,
                'total' => $total,
                'totalPages' => $totalPages,
                'currentPage' => $page
            ];
            
        } catch (Exception $e) {
            // Return empty result with pagination structure on error
            return [
                'spots' => [],
                'total' => 0,
                'totalPages' => 0,
                'currentPage' => $page
            ];
        }
    }

    /**
     * Generates pagination links HTML
     *
     * @param int $currentPage Current page number
     * @param int $totalPages Total number of pages
     * @param string $baseUrl Base URL for pagination links
     * @return string HTML for pagination controls
     */
    public static function generatePaginationLinks($currentPage, $totalPages, $baseUrl)
    {
        if ($totalPages <= 1) {
            return ''; // No pagination needed
        }
        
        $links = '<ul class="pagination">';
        
        // Previous button
        if ($currentPage > 1) {
            $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">Précédent</a></li>';
        } else {
            $links .= '<li class="page-item disabled"><a class="page-link">Précédent</a></li>';
        }
        
        // Page numbers
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $startPage + 4);
        
        if ($startPage > 1) {
            $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1">1</a></li>';
            if ($startPage > 2) {
                $links .= '<li class="page-item disabled"><a class="page-link">...</a></li>';
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $currentPage) {
                $links .= '<li class="page-item active"><a class="page-link">' . $i . '</a></li>';
            } else {
                $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
            }
        }
        
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $links .= '<li class="page-item disabled"><a class="page-link">...</a></li>';
            }
            $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">Suivant</a></li>';
        } else {
            $links .= '<li class="page-item disabled"><a class="page-link">Suivant</a></li>';
        }
        
        $links .= '</ul>';
        
        return $links;
    }

    /**
     * Update the status of a parking spot
     * 
     * @param int $id ID de la place
     * @param string $status Nouveau statut
     * @return bool Succès ou échec
     */
    public static function updateStatus($id, $status)
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("UPDATE places_parking SET statut = :statut WHERE id = :id");
            $stmt->bindParam(':statut', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut de la place de parking: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouve une place de parking par son ID
     *
     * @param int $id ID de la place de parking
     * @return ParkingSpot|null L'objet place de parking ou null si non trouvé
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM places_parking WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        
        $spot = new ParkingSpot();
        foreach ($data as $key => $value) {
            $spot->$key = $value;
        }
        
        // Ajout de la propriété tarif_horaire
        $spot->tarif_horaire = $data['tarif_horaire'];
        
        return $spot;
    }
    
    /**
     * Marque une place de parking comme libre
     *
     * @return bool Succès ou échec de l'opération
     */
    public function markAsFree() {
        $this->statut = 'disponible';
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("UPDATE places_parking SET statut = :statut WHERE id = :id");
            $stmt->bindParam(':statut', $this->statut);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut de la place de parking: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marque une place de parking comme occupée
     *
     * @return bool Succès ou échec de l'opération
     */
    public function markAsOccupied() {
        $this->statut = 'occupée';
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("UPDATE places_parking SET statut = :statut WHERE id = :id");
            $stmt->bindParam(':statut', $this->statut);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut de la place de parking: " . $e->getMessage());
            return false;
        }
    }
}
?>
