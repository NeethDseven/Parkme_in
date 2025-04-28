<?php

/**
 * Parking Model
 * 
 * This class represents the Parking model to handle parking-related data and operations
 */
class Parking
{
    private $db;

    /**
     * Constructor - initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all available parkings
     * 
     * @return array Array of parking data
     */
    public function getAllParkings()
    {
        $query = "SELECT * FROM parkings";
        return $this->db->query($query);
    }

    /**
     * Get parking by ID
     * 
     * @param int $id Parking ID
     * @return array|bool Parking data or false if not found
     */
    public function getParkingById($id)
    {
        $query = "SELECT * FROM parkings WHERE id = :id";
        return $this->db->query($query, ['id' => $id])->fetch();
    }

    /**
     * Get parking by ID - static version
     * 
     * @param int $id Parking ID
     * @return array|bool Parking data or false if not found
     */
    public static function getById($id)
    {
        $db = Database::getInstance();
        // Use direct integer casting instead of parameter binding for the ID
        // This is a workaround for the Database class implementation
        $id = (int)$id;
        $query = "SELECT * FROM parkings WHERE id = $id";
        return $db->query($query)->fetch();
    }

    /**
     * Get featured parkings
     * 
     * @param int $limit Optional limit for number of featured parkings to return
     * @return array Array of featured parking data
     */
    public static function getFeatured($limit = 6)
    {
        $db = Database::getInstance();
        // Modified query to not use the non-existent is_featured column
        // Instead, just get the most recent or highest rated parkings
        $query = "SELECT * FROM parkings ORDER BY id DESC LIMIT " . (int)$limit;
        return $db->query($query);
    }

    /**
     * Get all parkings - static version
     * 
     * @return array Array of all parking data
     */
    public static function getAll()
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM parkings";
        $statement = $db->query($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get parking information for a specific parking spot
     * 
     * @param int $spotId Parking spot ID
     * @return array|bool Parking data or false if not found
     */
    public static function getParkingInfoForSpot($spotId)
    {
        $db = Database::getInstance();
        $spotId = (int)$spotId;
        
        // First get the parking_id from the emplacements table
        $query = "SELECT parking_id FROM emplacements WHERE id = $spotId";
        $result = $db->query($query)->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || !isset($result['parking_id'])) {
            return false;
        }
        
        // Then get the parking information
        $parkingId = (int)$result['parking_id'];
        $query = "SELECT * FROM parkings WHERE id = $parkingId";
        return $db->query($query)->fetch(PDO::FETCH_ASSOC);
    }
    
    // Add other parking-related methods as needed
}
?>
