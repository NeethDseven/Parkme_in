<?php

class UserPreferences {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère les préférences de paiement d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Préférences de paiement
     */
    public function getPaymentPreferences($userId) {
        $stmt = $this->db->prepare("SELECT payment_preferences FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $preferences = $stmt->fetchColumn();
        
        if ($preferences) {
            $decoded = json_decode($preferences, true);
            return isset($decoded['payment']) ? $decoded['payment'] : [
                'default_method' => 'carte',
                'save_card_info' => false
            ];
        }
        
        return [
            'default_method' => 'carte',
            'save_card_info' => false
        ];
    }
    
    /**
     * Met à jour les préférences de paiement d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $preferences Nouvelles préférences
     * @return bool Succès de l'opération
     */
    public function updatePaymentPreferences($userId, $preferences) {
        // Récupérer d'abord les préférences existantes
        $stmt = $this->db->prepare("SELECT payment_preferences FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $currentPreferencesJson = $stmt->fetchColumn();
        
        $currentPreferences = $currentPreferencesJson ? json_decode($currentPreferencesJson, true) : [];
        
        // Mettre à jour seulement la section payment
        $currentPreferences['payment'] = $preferences;
        
        // Enregistrer les préférences mises à jour
        $stmt = $this->db->prepare("UPDATE users SET payment_preferences = ? WHERE id = ?");
        return $stmt->execute([json_encode($currentPreferences), $userId]);
    }
    
    /**
     * Récupère l'historique des réservations d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $limit Nombre maximum de réservations à récupérer
     * @return array Liste des réservations
     */
    public function getUserReservations($userId, $limit = 0) {
        $sql = "
            SELECT r.*, p.numero as place_numero, p.type as place_type
            FROM reservations r
            JOIN parking_spaces p ON r.place_id = p.id
            WHERE r.user_id = ?
            ORDER BY r.date_debut DESC
        ";
        
        if ($limit > 0) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

?>