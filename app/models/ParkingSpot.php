<?php
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../app/models/Database.php';

class ParkingSpot extends Model {
    // Propriétés
    public $id;
    public $numero;
    public $type;
    public $statut;
    public $parking_id;
    
    protected static $table = 'places_parking';
    
    /**
     * Récupérer toutes les places de parking
     *
     * @return array Liste des places de parking
     */
    public static function findAll() {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->query("SELECT * FROM places_parking ORDER BY numero");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in findAll places: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer une place de parking par son ID
     *
     * @param int $id ID de la place de parking
     * @return object|null Objet ParkingSpot ou null si non trouvé
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("SELECT * FROM places_parking WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }
            
            $spot = new self();
            foreach ($data as $key => $value) {
                $spot->$key = $value;
            }
            
            return $spot;
        } catch (PDOException $e) {
            error_log("Error in findById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer les places de parking disponibles pour un parking donné
     *
     * @param int $parkingId ID du parking
     * @return array Liste des places disponibles
     */
    public static function getAvailableByParkingId($parkingId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // On récupère toutes les places qui ne sont pas hors service
            // Les places occupées sont incluses car elles peuvent être réservées pour des dates futures
            $stmt = $conn->prepare("
                SELECT * FROM places_parking
                WHERE parking_id = :parking_id 
                AND statut != 'hors_service'
                ORDER BY numero
            ");
            $stmt->bindParam(':parking_id', $parkingId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAvailableByParkingId: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer toutes les places d'un parking
     *
     * @param int $parkingId ID du parking
     * @return array Liste des places
     */
    public static function getAllByParkingId($parkingId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT * FROM places_parking
                WHERE parking_id = :parking_id
                ORDER BY numero
            ");
            $stmt->bindParam(':parking_id', $parkingId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllByParkingId: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les places de parking disponibles pour une période donnée
     *
     * @param int $parkingId ID du parking
     * @param string $startDate Date de début (format Y-m-d H:i:s)
     * @param string $endDate Date de fin (format Y-m-d H:i:s)
     * @return array Liste des places disponibles
     */
    public static function getAvailableSpotsByPeriod($parkingId, $startDate, $endDate) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Ajout de logs de debug
            error_log("Recherche de places pour parking ID: {$parkingId}, début: {$startDate}, fin: {$endDate}");
            
            // 1. Récupérer toutes les places du parking
            $queryAllSpots = "SELECT * FROM places_parking WHERE parking_id = :parking_id AND statut != 'hors_service'";
            $stmtAllSpots = $conn->prepare($queryAllSpots);
            $stmtAllSpots->bindParam(':parking_id', $parkingId, PDO::PARAM_INT);
            $stmtAllSpots->execute();
            $allSpots = $stmtAllSpots->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Nombre total de places trouvées dans le parking: " . count($allSpots));
            
            $availableSpots = [];
            
            // 2. Vérifier individuellement la disponibilité de chaque place
            foreach ($allSpots as $spot) {
                // Requête pour vérifier les conflits de réservations
                $queryConflicts = "
                    SELECT COUNT(*) as count 
                    FROM reservations 
                    WHERE emplacement_id = :spot_id 
                    AND statut IN ('en_attente', 'confirmée') 
                    AND (
                        (date_debut < :end_date AND date_fin > :start_date)
                    )
                ";
                
                $stmtConflicts = $conn->prepare($queryConflicts);
                $stmtConflicts->bindParam(':spot_id', $spot['id'], PDO::PARAM_INT);
                $stmtConflicts->bindParam(':start_date', $startDate);
                $stmtConflicts->bindParam(':end_date', $endDate);
                $stmtConflicts->execute();
                
                $result = $stmtConflicts->fetch(PDO::FETCH_ASSOC);
                
                // Si aucun conflit, cette place est disponible
                if ($result['count'] == 0) {
                    $availableSpots[] = $spot;
                    error_log("Place ID {$spot['id']} (numéro {$spot['numero']}) est disponible.");
                } else {
                    error_log("Place ID {$spot['id']} (numéro {$spot['numero']}) n'est PAS disponible.");
                }
            }
            
            error_log("Nombre de places disponibles trouvées: " . count($availableSpots));
            return $availableSpots;
            
        } catch (PDOException $e) {
            error_log("Erreur dans getAvailableSpotsByPeriod: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Vérifier si une place est disponible pour une période donnée
     *
     * @param int $spotId ID de la place
     * @param string $startDate Date de début (format Y-m-d H:i:s)
     * @param string $endDate Date de fin (format Y-m-d H:i:s)
     * @return bool True si disponible, false sinon
     */
    public static function isSpotAvailableForPeriod($spotId, $startDate, $endDate) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Vérifier d'abord que la place existe et n'est pas hors service
            $spotStmt = $conn->prepare("SELECT statut FROM places_parking WHERE id = :id");
            $spotStmt->bindParam(':id', $spotId, PDO::PARAM_INT);
            $spotStmt->execute();
            $spotData = $spotStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$spotData) {
                error_log("La place ID {$spotId} n'existe pas.");
                return false;
            }
            
            if ($spotData['statut'] === 'hors_service') {
                error_log("La place ID {$spotId} est hors service.");
                return false;
            }
            
            // Vérifier les réservations existantes avec une requête simplifiée et debuggée
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count
                FROM reservations
                WHERE emplacement_id = :spot_id
                AND statut IN ('en_attente', 'confirmée')
                AND (
                    (date_debut < :end_date AND date_fin > :start_date)
                )
            ");
            
            $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $isAvailable = ($result['count'] == 0);
            
            error_log("Vérification de disponibilité pour place {$spotId}: " . 
                      ($isAvailable ? 'DISPONIBLE' : 'NON DISPONIBLE') . 
                      " ({$result['count']} conflits)");
            
            return $isAvailable;
        } catch (PDOException $e) {
            error_log("Erreur dans isSpotAvailableForPeriod: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crée une nouvelle place de parking
     *
     * @param string $numero Numéro de la place
     * @param string $type Type de place (normale, handicapée, etc.)
     * @param string $statut Statut initial (libre, occupée)
     * @param int $parkingId ID du parking (optionnel)
     * @return bool Succès ou échec de la création
     */
    public static function create($numero, $type = 'normale', $statut = 'libre', $parkingId = 1) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO places_parking (numero, type, statut, parking_id)
                VALUES (:numero, :type, :statut, :parking_id)
            ");
            
            $stmt->bindParam(':numero', $numero);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':statut', $statut);
            $stmt->bindParam(':parking_id', $parkingId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in create spot: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer une place de parking
     *
     * @param int $id ID de la place à supprimer
     * @return bool Succès ou échec de la suppression
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("DELETE FROM places_parking WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting spot: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour le statut d'une place de parking
     *
     * @param int $spotId ID de la place
     * @param string $status Nouveau statut ('libre', 'occupee', 'hors_service')
     * @return bool Succès ou échec
     */
    public static function updateStatus($spotId, $status) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $validStatuses = ['libre', 'occupee', 'hors_service'];
            if (!in_array($status, $validStatuses)) {
                error_log("Invalid status: $status");
                return false;
            }
            
            $stmt = $conn->prepare("UPDATE places_parking SET statut = :statut WHERE id = :id");
            $stmt->bindParam(':statut', $status);
            $stmt->bindParam(':id', $spotId, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            error_log("Updated spot $spotId status to $status: " . ($result ? 'success' : 'failed'));
            return $result;
        } catch (PDOException $e) {
            error_log("Error in updateStatus: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marque cette place comme occupée
     *
     * @return bool Succès ou échec
     */
    public function markAsOccupied() {
        return self::updateStatus($this->id, 'occupée');
    }
    
    /**
     * Marque cette place comme libre
     *
     * @return bool Succès ou échec
     */
    public function markAsFree() {
        return self::updateStatus($this->id, 'libre');
    }
}
?>
