<?php

class Review extends BaseModel {
    protected static $table = 'avis';
    protected static $fillable = [
        'utilisateur_id', 'parking_id', 'note', 'commentaire', 'date_avis'
    ];
    
    // Get user who wrote the review
    public function getUser() {
        return User::findById($this->utilisateur_id);
    }
    
    // Get parking for this review
    public function getParking() {
        return Parking::findById($this->parking_id);
    }
    
    // Check if a user has already reviewed a parking
    public static function hasUserReviewed($userId, $parkingId) {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as count FROM avis WHERE utilisateur_id = :user_id AND parking_id = :parking_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':parking_id', $parkingId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
