<?php

class UserPreferences {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function savePaymentPreferences($userId, $preferences) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET payment_preferences = ? 
            WHERE id = ?
        ");
        return $stmt->execute([json_encode($preferences), $userId]);
    }

    public function getPaymentPreferences($userId) {
        $stmt = $this->db->prepare("SELECT payment_preferences FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return json_decode($result['payment_preferences'] ?? '{}', true);
    }
}

?>