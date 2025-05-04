<?php

abstract class BaseModel {
    protected static $table;
    protected static $fillable = [];
    
    /**
     * Récupérer tous les enregistrements de la table
     *
     * @return array Liste des enregistrements
     */
    public static function findAll() {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $table = static::$table;
            $stmt = $conn->query("SELECT * FROM {$table}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in findAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer un enregistrement par son ID
     *
     * @param int $id ID de l'enregistrement
     * @return array|null Données de l'enregistrement ou null si non trouvé
     */
    public static function find($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $table = static::$table;
            $stmt = $conn->prepare("SELECT * FROM {$table} WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error in find: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Créer un nouvel enregistrement
     *
     * @param array $data Données à insérer
     * @return int|bool ID de l'enregistrement créé ou false en cas d'échec
     */
    public static function insert($data) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $table = static::$table;
            
            // Filtrer les données selon les champs autorisés
            $filteredData = [];
            foreach ($data as $key => $value) {
                if (in_array($key, static::$fillable)) {
                    $filteredData[$key] = $value;
                }
            }
            
            if (empty($filteredData)) {
                return false;
            }
            
            // Construire la requête
            $columns = implode(', ', array_keys($filteredData));
            $placeholders = ':' . implode(', :', array_keys($filteredData));
            
            $stmt = $conn->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
            
            // Lier les valeurs
            foreach ($filteredData as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            if ($stmt->execute()) {
                return $conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error in insert: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour un enregistrement
     *
     * @param int $id ID de l'enregistrement
     * @param array $data Données à mettre à jour
     * @return bool Succès ou échec
     */
    public static function update($id, $data) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $table = static::$table;
            
            // Filtrer les données selon les champs autorisés
            $filteredData = [];
            foreach ($data as $key => $value) {
                if (in_array($key, static::$fillable)) {
                    $filteredData[$key] = $value;
                }
            }
            
            if (empty($filteredData)) {
                return false;
            }
            
            // Construire la requête
            $setClause = '';
            foreach ($filteredData as $key => $value) {
                $setClause .= "{$key} = :{$key}, ";
            }
            $setClause = rtrim($setClause, ', ');
            
            $stmt = $conn->prepare("UPDATE {$table} SET {$setClause} WHERE id = :id");
            
            // Lier les valeurs
            foreach ($filteredData as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in update: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un enregistrement
     *
     * @param int $id ID de l'enregistrement à supprimer
     * @return bool Succès ou échec
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $table = static::$table;
            $stmt = $conn->prepare("DELETE FROM {$table} WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Compter le nombre d'enregistrements dans la table
     *
     * @param string|null $where Clause WHERE (optionnelle)
     * @param array $params Paramètres pour la clause WHERE
     * @return int Nombre d'enregistrements
     */
    public static function count($where = null, $params = []) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $table = static::$table;
            $query = "SELECT COUNT(*) FROM {$table}";
            
            if ($where) {
                $query .= " WHERE {$where}";
            }
            
            $stmt = $conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in count: " . $e->getMessage());
            return 0;
        }
    }
}
