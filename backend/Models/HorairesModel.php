<?php
namespace App\Models;

class HorairesModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::connect();
    }
    
    public function getHorairesOuverture() {
        $stmt = $this->db->query("SELECT * FROM horaires_ouverture ORDER BY jour_semaine");
        return $stmt->fetchAll();
    }
    
    public function updateHoraires($jour, $heureOuverture, $heureFermeture) {
        $stmt = $this->db->prepare("
            UPDATE horaires_ouverture 
            SET heure_ouverture = ?, heure_fermeture = ?
            WHERE jour_semaine = ?
        ");
        return $stmt->execute([$heureOuverture, $heureFermeture, $jour]);
    }
    
    public function estOuvert($dateHeure = null) {
        if ($dateHeure === null) {
            $dateHeure = date('Y-m-d H:i:s');
        }
        
        $jourSemaine = date('N', strtotime($dateHeure)); // 1 (lundi) à 7 (dimanche)
        $heure = date('H:i', strtotime($dateHeure));
        
        $stmt = $this->db->prepare("
            SELECT * FROM horaires_ouverture 
            WHERE jour_semaine = ?
        ");
        $stmt->execute([$jourSemaine]);
        $horaire = $stmt->fetch();
        
        if (!$horaire) {
            return false;
        }
        
        return $heure >= $horaire['heure_ouverture'] && $heure <= $horaire['heure_fermeture'];
    }
}
