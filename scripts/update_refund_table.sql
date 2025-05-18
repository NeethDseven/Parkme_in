-- Script pour mettre à jour la table des remboursements

ALTER TABLE remboursements MODIFY COLUMN status ENUM('en_cours', 'effectué', 'refusé') DEFAULT 'en_cours';

-- Mettre à jour les valeurs existantes
UPDATE remboursements SET status = 'effectué' WHERE status = 'effectue';
UPDATE remboursements SET status = 'refusé' WHERE status = 'refuse';
