<?php
require_once 'core/BaseModel.php';

class User extends BaseModel {
    protected static $table = 'utilisateurs';
    protected static $primaryKey = 'id';

    // Define properties to avoid deprecation warnings
    public $id;
    public $nom;
    public $prenom;
    public $email;
    public $password;
    public $telephone;
    public $role;
    public $date_creation;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function findByEmail($email) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row; // Retourner directement le tableau associatif
        }
        
        return null;
    }

    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return array|null User data if found, null otherwise
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function createUser($nom, $prenom, $email, $password, $role = 'user') {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'password' => $hashed_password,
            'role' => $role
        ];
        return self::create($data);
    }
    
    public static function updateUser($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        return self::update($id, $data);
    }
    
    public static function isAdmin($userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT role FROM " . self::$table . " WHERE id=? AND role='admin'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Create a new user
     * 
     * @param array $userData User data array with all required fields
     * @return int|false The ID of the newly created user, or false on failure
     */
    public static function create($userData) {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Validate required fields
            $requiredFields = ['nom', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    error_log("Missing required field: $field");
                    return false;
                }
            }
            
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = :email");
            $stmt->bindValue(':email', $userData['email']);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                error_log("Email already exists: " . $userData['email']);
                return false;
            }
            
            // Hash password if it's not already hashed
            if (!password_get_info($userData['password'])['algo']) {
                $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }
            
            // Set default role if not provided
            if (!isset($userData['role'])) {
                $userData['role'] = 'user';
            }
            
            // Prepare SQL statement
            $fields = array_keys($userData);
            $placeholders = array_map(function($field) { return ":$field"; }, $fields);
            
            $sql = "INSERT INTO utilisateurs (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            foreach ($userData as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            
            // Execute the query
            $success = $stmt->execute();
            
            if ($success) {
                return $conn->lastInsertId();
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Database error during user creation: " . $errorInfo[2]);
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("Exception during user creation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate a user with email and password
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array|bool User data if authenticated, false otherwise
     */
    public function authenticate($email, $password)
    {
        // Sanitize email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        // Query to find user by email
        $query = "SELECT * FROM utilisateurs WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if user exists and verify password
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Save remember me token for a user
     * 
     * @param int $userId User ID
     * @param string $token Remember me token
     * @return bool Success or failure
     */
    public function saveRememberToken($userId, $token)
    {
        $userId = (int)$userId;
        
        $query = "UPDATE utilisateurs SET remember_token = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$token, $userId]);
    }
    
    /**
     * Get user by remember token
     * 
     * @param string $token Remember token
     * @return array|bool User data if found, false otherwise
     */
    public function getUserByRememberToken($token)
    {
        $query = "SELECT * FROM utilisateurs WHERE remember_token = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Register a new user
     * 
     * @param array $userData User data
     * @return int|bool New user ID if successful, false otherwise
     */
    public function register($userData)
    {
        // Hash the password
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO utilisateurs (email, mot_de_passe, nom, prenom) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        $success = $stmt->execute([
            $userData['email'],
            $passwordHash,
            $userData['nom'] ?? '',
            $userData['prenom'] ?? ''
        ]);
        
        if ($success) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
}
?>