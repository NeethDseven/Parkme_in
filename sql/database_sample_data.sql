-- Insertion de données de test pour la base de données parkme_in
-- Version: 1.0
-- Date: 2023-11-28

USE `parkme_in`;

-- Nettoyage des tables existantes (dans l'ordre inverse des dépendances)
DELETE FROM notifications;
DELETE FROM paiements;
DELETE FROM reservations;
DELETE FROM places_parking;
DELETE FROM parkings;
DELETE FROM utilisateurs;

-- Réinitialisation des compteurs
ALTER TABLE utilisateurs AUTO_INCREMENT = 1;
ALTER TABLE parkings AUTO_INCREMENT = 1;
ALTER TABLE places_parking AUTO_INCREMENT = 1;
ALTER TABLE reservations AUTO_INCREMENT = 1;
ALTER TABLE paiements AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;

-- Insertion d'utilisateurs de test
-- Note: Les mots de passe sont 'password123' hashés avec bcrypt
INSERT INTO `utilisateurs` (`nom`, `prenom`, `email`, `password`, `telephone`, `role`, `date_creation`) VALUES
('Admin', 'System', 'admin@parkme-in.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', 'admin', NOW() - INTERVAL 30 DAY),
('Dupont', 'Jean', 'jean.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345678', 'user', NOW() - INTERVAL 25 DAY),
('Martin', 'Sophie', 'sophie.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0723456789', 'user', NOW() - INTERVAL 20 DAY),
('Bernard', 'Thomas', 'thomas.bernard@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0687654321', 'user', NOW() - INTERVAL 15 DAY),
('Petit', 'Marie', 'marie.petit@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0745678912', 'user', NOW() - INTERVAL 10 DAY),
('Robert', 'Philippe', 'philippe.robert@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0698765432', 'user', NOW() - INTERVAL 5 DAY);

-- Insertion de parkings de test
INSERT INTO `parkings` (`nom`, `adresse`, `code_postal`, `ville`, `capacite`, `tarif_horaire`, `ouverture`, `fermeture`, `description`) VALUES
('Parking Centre-Ville', '1 Place de la Mairie', '75001', 'Paris', 50, 2.50, '07:00:00', '23:00:00', 'Parking situé en plein centre-ville, à proximité des commerces et des restaurants.'),
('Parking Gare', '10 Avenue de la Gare', '75010', 'Paris', 100, 3.00, '05:30:00', '00:00:00', 'Parking situé à côté de la gare, idéal pour les voyageurs.'),
('Parking Shopping', '25 Rue du Commerce', '75015', 'Paris', 80, 2.00, '08:00:00', '22:00:00', 'Grand parking couvert près du centre commercial.'),
('Parking Opéra', '5 Avenue de l\'Opéra', '75002', 'Paris', 30, 3.50, '08:00:00', '23:30:00', 'Parking souterrain proche de l\'Opéra et des grands magasins.'),
('Parking Montparnasse', '15 Boulevard du Montparnasse', '75006', 'Paris', 120, 2.80, '06:00:00', '01:00:00', 'Parking de grande capacité proche de la tour Montparnasse.');

-- Insertion de places de parking de test
-- Pour le Parking Centre-Ville
INSERT INTO `places_parking` (`parking_id`, `numero`, `type`, `statut`) VALUES
(1, 'A-01', 'normale', 'libre'),
(1, 'A-02', 'normale', 'libre'),
(1, 'A-03', 'handicapee', 'libre'),
(1, 'A-04', 'normale', 'occupee'),
(1, 'A-05', 'normale', 'libre'),
(1, 'B-01', 'normale', 'libre'),
(1, 'B-02', 'normale', 'occupee'),
(1, 'B-03', 'handicapee', 'libre'),
(1, 'B-04', 'normale', 'libre'),
(1, 'B-05', 'normale', 'hors_service');

-- Pour le Parking Gare
INSERT INTO `places_parking` (`parking_id`, `numero`, `type`, `statut`) VALUES
(2, 'A-01', 'normale', 'libre'),
(2, 'A-02', 'normale', 'libre'),
(2, 'A-03', 'handicapee', 'libre'),
(2, 'A-04', 'normale', 'occupee'),
(2, 'A-05', 'normale', 'libre'),
(2, 'B-01', 'normale', 'libre'),
(2, 'B-02', 'normale', 'occupee'),
(2, 'B-03', 'handicapee', 'libre'),
(2, 'B-04', 'normale', 'libre'),
(2, 'B-05', 'normale', 'libre'),
(2, 'C-01', 'normale', 'libre'),
(2, 'C-02', 'normale', 'libre'),
(2, 'C-03', 'normale', 'occupee'),
(2, 'C-04', 'handicapee', 'libre'),
(2, 'C-05', 'normale', 'libre');

-- Pour le Parking Shopping
INSERT INTO `places_parking` (`parking_id`, `numero`, `type`, `statut`) VALUES
(3, 'A-01', 'normale', 'libre'),
(3, 'A-02', 'normale', 'libre'),
(3, 'A-03', 'handicapee', 'libre'),
(3, 'A-04', 'normale', 'occupee'),
(3, 'A-05', 'normale', 'libre'),
(3, 'B-01', 'normale', 'libre'),
(3, 'B-02', 'normale', 'occupee'),
(3, 'B-03', 'handicapee', 'libre'),
(3, 'B-04', 'normale', 'libre'),
(3, 'B-05', 'normale', 'libre');

-- Pour le Parking Opéra
INSERT INTO `places_parking` (`parking_id`, `numero`, `type`, `statut`) VALUES
(4, 'A-01', 'normale', 'libre'),
(4, 'A-02', 'normale', 'occupee'),
(4, 'A-03', 'handicapee', 'libre'),
(4, 'A-04', 'normale', 'occupee'),
(4, 'A-05', 'normale', 'libre'),
(4, 'B-01', 'normale', 'libre'),
(4, 'B-02', 'normale', 'occupee'),
(4, 'B-03', 'handicapee', 'libre');

-- Pour le Parking Montparnasse
INSERT INTO `places_parking` (`parking_id`, `numero`, `type`, `statut`) VALUES
(5, 'A-01', 'normale', 'libre'),
(5, 'A-02', 'normale', 'libre'),
(5, 'A-03', 'handicapee', 'libre'),
(5, 'A-04', 'normale', 'occupee'),
(5, 'A-05', 'normale', 'libre'),
(5, 'B-01', 'normale', 'libre'),
(5, 'B-02', 'normale', 'occupee'),
(5, 'B-03', 'handicapee', 'libre'),
(5, 'B-04', 'normale', 'libre'),
(5, 'B-05', 'normale', 'libre'),
(5, 'C-01', 'normale', 'libre'),
(5, 'C-02', 'normale', 'libre'),
(5, 'C-03', 'normale', 'libre'),
(5, 'C-04', 'normale', 'libre'),
(5, 'C-05', 'normale', 'libre');

-- Insertion de réservations de test
-- Réservations actives
INSERT INTO `reservations` (`utilisateur_id`, `emplacement_id`, `date_debut`, `date_fin`, `date_reservation`, `statut`, `prix`, `code_acces`, `vehicule`) VALUES
(2, 4, NOW() - INTERVAL 1 HOUR, NOW() + INTERVAL 2 HOUR, NOW() - INTERVAL 1 DAY, 'confirmée', 7.50, 'A1B2C3', 'Renault Clio - AB-123-CD'),
(3, 7, NOW() - INTERVAL 2 HOUR, NOW() + INTERVAL 1 HOUR, NOW() - INTERVAL 2 DAY, 'confirmée', 9.00, 'D4E5F6', 'Peugeot 208 - CD-456-EF'),
(4, 14, NOW() - INTERVAL 30 MINUTE, NOW() + INTERVAL 3 HOUR, NOW() - INTERVAL 12 HOUR, 'confirmée', 10.50, 'G7H8I9', 'Toyota Yaris - EF-789-GH');

-- Réservations à venir
INSERT INTO `reservations` (`utilisateur_id`, `emplacement_id`, `date_debut`, `date_fin`, `date_reservation`, `statut`, `prix`, `code_acces`, `vehicule`) VALUES
(2, 21, NOW() + INTERVAL 1 DAY, NOW() + INTERVAL 1 DAY + INTERVAL 3 HOUR, NOW() - INTERVAL 2 DAY, 'confirmée', 7.50, 'J0K1L2', 'Renault Clio - AB-123-CD'),
(3, 26, NOW() + INTERVAL 2 DAY, NOW() + INTERVAL 2 DAY + INTERVAL 4 HOUR, NOW() - INTERVAL 1 DAY, 'confirmée', 12.00, 'M3N4O5', 'Peugeot 308 - GH-101-IJ'),
(4, 33, NOW() + INTERVAL 3 DAY, NOW() + INTERVAL 3 DAY + INTERVAL 2 HOUR, NOW(), 'confirmée', 5.60, 'P6Q7R8', 'Ford Fiesta - IJ-202-KL');

-- Réservations terminées
INSERT INTO `reservations` (`utilisateur_id`, `emplacement_id`, `date_debut`, `date_fin`, `date_reservation`, `statut`, `prix`, `code_acces`, `vehicule`) VALUES
(2, 17, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 5 DAY + INTERVAL 2 HOUR, NOW() - INTERVAL 6 DAY, 'terminée', 6.00, 'S9T0U1', 'Renault Clio - AB-123-CD'),
(3, 22, NOW() - INTERVAL 4 DAY, NOW() - INTERVAL 4 DAY + INTERVAL 3 HOUR, NOW() - INTERVAL 5 DAY, 'terminée', 9.00, 'V2W3X4', 'Peugeot 208 - CD-456-EF'),
(4, 27, NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY + INTERVAL 1 HOUR, NOW() - INTERVAL 4 DAY, 'terminée', 2.80, 'Y5Z6A7', 'Toyota Yaris - EF-789-GH'),
(5, 32, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY + INTERVAL 4 HOUR, NOW() - INTERVAL 3 DAY, 'terminée', 14.00, 'B8C9D0', 'Volkswagen Golf - KL-303-MN'),
(2, 18, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY + INTERVAL 2 HOUR, NOW() - INTERVAL 2 DAY, 'terminée', 5.00, 'E1F2G3', 'Renault Clio - AB-123-CD');

-- Réservations annulées
INSERT INTO `reservations` (`utilisateur_id`, `emplacement_id`, `date_debut`, `date_fin`, `date_reservation`, `statut`, `prix`, `code_acces`, `vehicule`) VALUES
(3, 23, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY + INTERVAL 2 HOUR, NOW() - INTERVAL 12 DAY, 'annulée', 6.00, 'H4I5J6', 'Peugeot 208 - CD-456-EF'),
(4, 28, NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 8 DAY + INTERVAL 3 HOUR, NOW() - INTERVAL 9 DAY, 'annulée', 9.00, 'K7L8M9', 'Toyota Yaris - EF-789-GH');

-- Insertion de paiements de test
INSERT INTO `paiements` (`reservation_id`, `utilisateur_id`, `montant`, `methode`, `statut`, `transaction_id`, `date_paiement`) VALUES
(1, 2, 7.50, 'carte', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 1 DAY),
(2, 3, 9.00, 'carte', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 2 DAY),
(3, 4, 10.50, 'paypal', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 12 HOUR),
(4, 2, 7.50, 'carte', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 2 DAY),
(5, 3, 12.00, 'carte', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 1 DAY),
(6, 4, 5.60, 'paypal', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW()),
(7, 2, 6.00, 'carte', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 6 DAY),
(8, 3, 9.00, 'carte', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 5 DAY),
(9, 4, 2.80, 'carte', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 4 DAY),
(10, 5, 14.00, 'paypal', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 3 DAY),
(11, 2, 5.00, 'carte', 'complete', 'TRX' || FLOOR(RAND() * 1000000), NOW() - INTERVAL 2 DAY);

-- Insertion de notifications de test
INSERT INTO `notifications` (`utilisateur_id`, `message`, `type`, `lu`, `date_creation`) VALUES
(2, 'Votre réservation #1 a été confirmée. Votre code d\'accès est A1B2C3.', 'reservation', 1, NOW() - INTERVAL 1 DAY),
(2, 'Rappel: Votre réservation #1 commence dans 1 heure.', 'rappel', 0, NOW() - INTERVAL 2 HOUR),
(2, 'Votre réservation #4 a été confirmée. Votre code d\'accès est J0K1L2.', 'reservation', 0, NOW() - INTERVAL 2 DAY),
(2, 'Votre paiement de 7.50 € pour la réservation #4 a été confirmé.', 'paiement', 1, NOW() - INTERVAL 2 DAY),
(3, 'Votre réservation #2 a été confirmée. Votre code d\'accès est D4E5F6.', 'reservation', 1, NOW() - INTERVAL 2 DAY),
(3, 'Votre réservation #5 a été confirmée. Votre code d\'accès est M3N4O5.', 'reservation', 0, NOW() - INTERVAL 1 DAY),
(3, 'Votre paiement de 12.00 € pour la réservation #5 a été confirmé.', 'paiement', 1, NOW() - INTERVAL 1 DAY),
(4, 'Votre réservation #3 a été confirmée. Votre code d\'accès est G7H8I9.', 'reservation', 1, NOW() - INTERVAL 12 HOUR),
(4, 'Rappel: Votre réservation #3 commence dans 30 minutes.', 'rappel', 0, NOW() - INTERVAL 1 HOUR),
(4, 'Votre réservation #6 a été confirmée. Votre code d\'accès est P6Q7R8.', 'reservation', 0, NOW()),
(5, 'Votre réservation #10 a été confirmée. Votre code d\'accès est B8C9D0.', 'reservation', 1, NOW() - INTERVAL 3 DAY),
(5, 'Votre paiement de 14.00 € pour la réservation #10 a été confirmé.', 'paiement', 1, NOW() - INTERVAL 3 DAY);

COMMIT;
