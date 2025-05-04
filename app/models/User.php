<?php
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../app/models/Database.php';

class User extends Model {
    protected static $table = 'utilisateurs';
    protected static $fillable = [
        'nom', 'prenom', 'email', 'password', 'telephone', 'role', 'date_creation'
    ];

    /**
     * Récupérer un utilisateur par son ID
     *
     * @param int $id ID de l'utilisateur
     * @return array|null Données de l'utilisateur ou null si non trouvé
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user : null;
        } catch (PDOException $e) {
            error_log("Error in findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer un utilisateur par son email
     *
     * @param string $email Email de l'utilisateur
     * @return array|null Données de l'utilisateur ou null si non trouvé
     */
    public static function findByEmail($email) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user : null;
        } catch (PDOException $e) {
            error_log("Error in findByEmail: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer tous les utilisateurs
     *
     * @return array Tableau de tous les utilisateurs
     */
    public static function findAll() {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->query("SELECT * FROM utilisateurs ORDER BY date_creation DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in findAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Créer un nouvel utilisateur
     *
     * @param string $nom Nom de l'utilisateur
     * @param string $prenom Prénom de l'utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur (en clair)
     * @param string $role Rôle de l'utilisateur (défaut: 'user')
     * @return bool Succès ou échec de la création
     */
    public static function createUser($nom, $prenom, $email, $password, $role = 'user') {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            return false; // L'email existe déjà
        }
        
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, password, role, date_creation)
                VALUES (:nom, :prenom, :email, :password, :role, NOW())
            ");
            
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in createUser: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour un utilisateur existant
     *
     * @param int $id ID de l'utilisateur
     * @param array $data Données à mettre à jour
     * @return bool Succès ou échec de la mise à jour
     */
    public static function updateUser($id, $data) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $query = "UPDATE utilisateurs SET ";
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($key === 'password' && !empty($value)) {
                    $params[] = "$key = :$key";
                    $data[$key] = password_hash($value, PASSWORD_DEFAULT);
                } elseif ($key !== 'password') {
                    $params[] = "$key = :$key";
                }
            }
            
            $query .= implode(", ", $params);
            $query .= " WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            
            foreach ($data as $key => $value) {
                if ($key === 'password' && empty($value)) {
                    continue;
                }
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in updateUser: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour le mot de passe d'un utilisateur
     *
     * @param int $id ID de l'utilisateur
     * @param string $hashedPassword Mot de passe déjà hashé
     * @return bool Succès ou échec de la mise à jour
     */
    public static function updatePassword($id, $hashedPassword) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("UPDATE utilisateurs SET password = :password WHERE id = :id");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in updatePassword: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour un utilisateur
     *
     * @param int $id ID de l'utilisateur
     * @param array $data Données à mettre à jour
     * @return bool Succès ou échec de la mise à jour
     */
    public static function update($id, $data) {
        return self::updateUser($id, $data);
    }
    
    /**
     * Supprimer un utilisateur
     *
     * @param int $id ID de l'utilisateur à supprimer
     * @return bool Succès ou échec de la suppression
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier si un email existe déjà
     *
     * @param string $email Email à vérifier
     * @param int|null $excludeId ID de l'utilisateur à exclure de la vérification
     * @return bool True si l'email existe déjà, false sinon
     */
    public static function emailExists($email, $excludeId = null) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            if ($excludeId) {
                $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = :email AND id != :id");
                $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
            } else {
                $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = :email");
            }
            
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log("Error in emailExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée un nouvel utilisateur
     * 
     * @param string $nom Nom de l'utilisateur
     * @param string $prenom Prénom de l'utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $password_hash Mot de passe haché
     * @param string $telephone Téléphone de l'utilisateur (optionnel)
     * @return int|bool ID de l'utilisateur créé ou false en cas d'échec
     */
    public static function create($nom, $prenom, $email, $password_hash, $telephone = null) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            // Vérifier si l'email existe déjà
            if (self::emailExists($email)) {
                return false;
            }
            
            $stmt = $conn->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, password, telephone, role, date_creation)
                VALUES (:nom, :prenom, :email, :password, :telephone, 'user', NOW())
            ");
            
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':telephone', $telephone);
            
            if ($stmt->execute()) {
                return $conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error in User::create: " . $e->getMessage());
            return false;
        }
    }
}
?>