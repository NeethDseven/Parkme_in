<?php
namespace App\Models;

class TarifsModel {
    private $db;
    
    public function __construct() {
        $this->db = \Database::connect();
    }
    
    public function getAllTarifs() {
        $stmt = $this->db->query("SELECT * FROM tarifs");
        return $stmt->fetchAll();
    }
    
    public function getTarifsByType($type) {
        $stmt = $this->db->prepare("SELECT * FROM tarifs WHERE type_place = ?");
        $stmt->execute([$type]);
        return $stmt->fetch();
    }
    
    public function updateTarifs($id, $prixHeure, $prixJournee, $prixMois) {
        $stmt = $this->db->prepare("
            UPDATE tarifs 
            SET prix_heure = ?, prix_journee = ?, prix_mois = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$prixHeure, $prixJournee, $prixMois, $id]);
    }
    
    public function calculatePrice($placeType, $dateDebut, $dateFin) {
        $tarif = $this->getTarifsByType($placeType);
        
        if (!$tarif) {
            throw new \Exception("Tarif non trouvé pour ce type de place");
        }
        
        $debut = new \DateTime($dateDebut);
        $fin = new \DateTime($dateFin);
        $dureeHeures = ($fin->getTimestamp() - $debut->getTimestamp()) / 3600;
        
        // Appliquer des tarifs différents selon le moment
        $jourSemaine = (int)$debut->format('N'); // 1 (lundi) à 7 (dimanche)
        $heure = (int)$debut->format('H');
        
        $multiplicateur = 1.0;
        // Weekend: vendredi soir, samedi, dimanche
        if ($jourSemaine >= 5 && ($jourSemaine > 5 || $heure >= 18)) {
            $multiplicateur = 1.2; // Majoration de 20%
        }
        // Nuit: 22h à 6h
        elseif ($heure >= 22 || $heure < 6) {
            $multiplicateur = 0.9; // Réduction de 10%
        }
        
        // Calcul du prix selon la durée
        if ($dureeHeures <= 24) {
            return round($dureeHeures * $tarif['prix_heure'] * $multiplicateur, 2);
        } else {
            $jours = ceil($dureeHeures / 24);
            return round($jours * $tarif['prix_journee'] * $multiplicateur, 2);
        }
    }
}
