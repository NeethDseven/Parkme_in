<?php
// Script de migration pour mettre à jour les statuts de remboursements existants

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::connect();
    echo "Connexion à la base de données réussie.<br>";
    
    // Mettre à jour les statuts 'effectue' vers 'effectué'
    $stmt = $db->prepare("UPDATE remboursements SET status = 'effectué' WHERE status = 'effectue'");
    $count1 = $stmt->execute() ? $stmt->rowCount() : 0;
    
    // Mettre à jour les statuts 'refuse' vers 'refusé'
    $stmt = $db->prepare("UPDATE remboursements SET status = 'refusé' WHERE status = 'refuse'");
    $count2 = $stmt->execute() ? $stmt->rowCount() : 0;
    
    echo "Mise à jour terminée. $count1 remboursements 'effectue' et $count2 remboursements 'refuse' ont été mis à jour.<br>";
    
    // Afficher les remboursements après mise à jour
    $stmt = $db->query("SELECT id, status FROM remboursements");
    echo "<h3>Statuts après mise à jour :</h3>";
    echo "<table border='1'><tr><th>ID</th><th>Statut</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['status']}</td></tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
