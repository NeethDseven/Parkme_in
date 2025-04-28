<?php

abstract class BaseModel {
    protected static $table;
    protected static $primaryKey = 'id';
    
    /**
     * Trouver tous les enregistrements d'une table
     *
     * @return array
     */
    public static function findAll() {
        // Use the getInstance method instead of direct instantiation
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->query("SELECT * FROM " . static::$table);
        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $row;
        }
        return $items;
    }
    
    /**
     * Trouver un enregistrement par sa clé primaire
     *
     * @param mixed $id Valeur de la clé primaire
     * @return array|null
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Créer un nouvel enregistrement
     *
     * @param array $data Données à insérer
     * @return bool
     */
    public static function create($data) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        
        $stmt = $conn->prepare("INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)");
        
        // Déterminer les types de données pour bind_param
        $types = "";
        $values = [];
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= "i";
            } elseif (is_float($value)) {
                $types .= "d";
            } else {
                $types .= "s";
            }
            $values[] = $value;
        }
        
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }
    
    /**
     * Get the table name from the class name
     * 
     * @param string $className The name of the class
     * @return string Table name corresponding to the class
     */
    protected static function getTableName($className) {
        // Map class names to table names
        $tableMap = [
            'User' => 'utilisateurs',
            'Parking' => 'parkings',
            'ParkingSpot' => 'places_parking',
            'Reservation' => 'reservations',
            'Payment' => 'paiements',
            'Review' => 'avis'
        ];
        
        // If the class exists in our map, return the corresponding table name
        if (isset($tableMap[$className])) {
            return $tableMap[$className];
        }
        
        // Default case: convert the class name to snake_case and make it plural
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        
        // Simple pluralization for English words (add 's')
        return $table . 's';
    }
    
    /**
     * Generic update method for any table
     * 
     * @param int $id The ID of the record to update
     * @param array $data The data to update (field => value pairs)
     * @param string $table Optional table name (defaults to derived table name)
     * @return bool True on success, false on failure
     */
    public static function update($id, $data, $table = null) {
        // If table name is not provided, derive it from the called class
        if ($table === null) {
            $calledClass = get_called_class();
            $table = self::getTableName($calledClass);
        }

        // No data to update
        if (empty($data)) {
            return false;
        }

        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Build the SET part of the query
        $sets = [];
        foreach ($data as $field => $value) {
            $sets[] = "$field = :$field";
        }
        $setClause = implode(', ', $sets);
        
        // Prepare the update statement
        $sql = "UPDATE $table SET $setClause WHERE id = :id";
        $stmt = $conn->prepare($sql);
        
        // Bind all parameters using PDO's bindParam or bindValue
        foreach ($data as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        $stmt->bindValue(':id', $id);
        
        // Execute the query
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Handle the error (log it, etc.)
            error_log("Database error in update: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un enregistrement
     *
     * @param mixed $id Valeur de la clé primaire
     * @return bool
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
