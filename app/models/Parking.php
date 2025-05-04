<?php
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../app/models/Database.php';

/**
 * Parking Model
 * 
 * This class represents the Parking model to handle parking-related data and operations
 */
class Parking extends Model {
    public $id;
    public $nom;
    public $adresse;
    public $code_postal;
    public $ville;
    public $capacite;
    public $tarif_horaire;
    public $ouverture;
    public $fermeture;
    public $description;
    
    protected static $table = 'parkings';
    
    /**
     * Récupérer un parking par son ID
     *
     * @param int $id ID du parking
     * @return array|null Informations sur le parking ou null si non trouvé
     */
    public static function getById($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT p.*,
                (SELECT COUNT(*) FROM places_parking pp WHERE pp.parking_id = p.id AND pp.statut = 'libre') as places_disponibles,
                (SELECT COUNT(*) FROM places_parking pp WHERE pp.parking_id = p.id) as places_totales
                FROM parkings p
                WHERE p.id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting parking by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer tous les parkings
     *
     * @return array Liste de tous les parkings
     */
    public static function getAll() {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Récupérer les parkings avec le nombre de places disponibles
            $stmt = $conn->query("
                SELECT p.*,
                (SELECT COUNT(*) FROM places_parking pp WHERE pp.parking_id = p.id AND pp.statut = 'libre') as places_disponibles,
                (SELECT COUNT(*) FROM places_parking pp WHERE pp.parking_id = p.id) as places_totales
                FROM parkings p
                ORDER BY p.nom
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all parkings: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les informations d'un parking pour une place spécifique
     *
     * @param int $spotId ID de la place de parking
     * @return array|null Informations sur le parking ou null
     */
    public static function getParkingInfoForSpot($spotId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT p.* FROM parkings p
                JOIN places_parking pp ON p.id = pp.parking_id
                WHERE pp.id = :spot_id
            ");
            $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting parking info for spot: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Créer un nouveau parking
     *
     * @param array $data Données du parking
     * @return int|false ID du nouveau parking ou false en cas d'échec
     */
    public static function create($data) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO parkings (nom, adresse, code_postal, ville, capacite, tarif_horaire, ouverture, fermeture, description)
                VALUES (:nom, :adresse, :code_postal, :ville, :capacite, :tarif_horaire, :ouverture, :fermeture, :description)
            ");
            
            $stmt->bindParam(':nom', $data['nom']);
            $stmt->bindParam(':adresse', $data['adresse']);
            $stmt->bindParam(':code_postal', $data['code_postal']);
            $stmt->bindParam(':ville', $data['ville']);
            $stmt->bindParam(':capacite', $data['capacite'], PDO::PARAM_INT);
            $stmt->bindParam(':tarif_horaire', $data['tarif_horaire']);
            $stmt->bindParam(':ouverture', $data['ouverture']);
            $stmt->bindParam(':fermeture', $data['fermeture']);
            $stmt->bindParam(':description', $data['description']);
            
            if ($stmt->execute()) {
                return $conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creating parking: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour un parking existant
     *
     * @param int $id ID du parking
     * @param array $data Données à mettre à jour
     * @return bool Succès ou échec de la mise à jour
     */
    public static function update($id, $data) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $query = "UPDATE parkings SET ";
            $params = [];
            
            foreach ($data as $key => $value) {
                $params[] = "$key = :$key";
            }
            
            $query .= implode(", ", $params);
            $query .= " WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating parking: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un parking
     *
     * @param int $id ID du parking à supprimer
     * @return bool Succès ou échec de la suppression
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("DELETE FROM parkings WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting parking: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Compter le nombre de places disponibles dans un parking
     *
     * @param int $parkingId ID du parking
     * @return int Nombre de places disponibles
     */
    public static function countAvailableSpots($parkingId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM places_parking 
                WHERE parking_id = :parking_id AND statut = 'libre'
            ");
            $stmt->bindParam(':parking_id', $parkingId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Error counting available spots: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Compter le nombre total de places dans un parking
     *
     * @param int $parkingId ID du parking
     * @return int Nombre total de places
     */
    public static function countTotalSpots($parkingId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM places_parking 
                WHERE parking_id = :parking_id
            ");
            $stmt->bindParam(':parking_id', $parkingId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Error counting total spots: " . $e->getMessage());
            return 0;
        }
    }
}
?>
