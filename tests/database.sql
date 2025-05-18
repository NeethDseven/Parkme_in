CREATE DATABASE IF NOT EXISTS parking_db_test;
USE parking_db_test;

-- Copier la structure de la base de données principale
-- mais avec des données de test minimales
INSERT INTO tarifs (type_place, prix_heure, prix_journee, prix_mois) VALUES
('standard', 2.00, 20.00, 200.00);
