<?php
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../app/models/Database.php';

class Payment extends Model {
    // Propriétés
    public $id;
    public $reservation_id;
    public $utilisateur_id;
    public $montant;
    public $methode;
    public $statut;
    public $transaction_id;
    public $date_paiement;
    
    protected static $table = 'paiements';
    
    /**
     * Créer un nouveau paiement
     *
     * @param int $reservationId ID de la réservation
     * @param int $userId ID de l'utilisateur
     * @param float $amount Montant du paiement
     * @param string $method Méthode de paiement (carte, paypal, etc.)
     * @param string $status Statut du paiement (en attente, complété, échoué)
     * @param string|null $transactionId ID de la transaction externe
     * @return int|bool ID du paiement créé ou false en cas d'échec
     */
    public static function create($reservationId, $userId, $amount, $method = 'carte', $status = 'en_attente', $transactionId = null) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO paiements (reservation_id, utilisateur_id, montant, methode, statut, transaction_id, date_paiement)
                VALUES (:reservation_id, :utilisateur_id, :montant, :methode, :statut, :transaction_id, NOW())
            ");
            
            $stmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':montant', $amount);
            $stmt->bindParam(':methode', $method);
            $stmt->bindParam(':statut', $status);
            $stmt->bindParam(':transaction_id', $transactionId);
            
            if ($stmt->execute()) {
                return $conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Erreur lors de la création du paiement: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour le statut d'un paiement
     *
     * @param int $id ID du paiement
     * @param string $status Nouveau statut
     * @param string|null $transactionId ID de la transaction externe (optionnel)
     * @return bool Succès ou échec de la mise à jour
     */
    public static function updateStatus($id, $status, $transactionId = null) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $query = "UPDATE paiements SET statut = :statut";
            
            if ($transactionId !== null) {
                $query .= ", transaction_id = :transaction_id";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':statut', $status);
            
            if ($transactionId !== null) {
                $stmt->bindParam(':transaction_id', $transactionId);
            }
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut du paiement: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer un paiement par son ID
     *
     * @param int $id ID du paiement
     * @return array|null Données du paiement ou null si non trouvé
     */
    public static function findById($id) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("SELECT * FROM paiements WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du paiement: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupérer tous les paiements d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return array Liste des paiements
     */
    public static function findByUserId($userId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("
                SELECT p.*, r.date_debut, r.date_fin
                FROM paiements p
                JOIN reservations r ON p.reservation_id = r.id
                WHERE p.utilisateur_id = :utilisateur_id
                ORDER BY p.date_paiement DESC
            ");
            
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des paiements: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer le paiement associé à une réservation
     *
     * @param int $reservationId ID de la réservation
     * @return array|null Données du paiement ou null si non trouvé
     */
    public static function findByReservationId($reservationId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("SELECT * FROM paiements WHERE reservation_id = :reservation_id");
            $stmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du paiement: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Calculer le total des revenus
     *
     * @param string|null $period Période (jour, semaine, mois, année)
     * @return float Total des revenus
     */
    public static function calculateRevenue($period = null) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        try {
            $query = "SELECT COALESCE(SUM(montant), 0) as total FROM paiements WHERE statut = 'complete'";
            
            if ($period !== null) {
                switch ($period) {
                    case 'day':
                        $query .= " AND DATE(date_paiement) = CURDATE()";
                        break;
                    case 'week':
                        $query .= " AND YEARWEEK(date_paiement, 1) = YEARWEEK(CURDATE(), 1)";
                        break;
                    case 'month':
                        $query .= " AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE())";
                        break;
                    case 'year':
                        $query .= " AND YEAR(date_paiement) = YEAR(CURDATE())";
                        break;
                }
            }
            
            $stmt = $conn->query($query);
            
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur lors du calcul des revenus: " . $e->getMessage());
            return 0;
        }
    }
}
