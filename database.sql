CREATE DATABASE IF NOT EXISTS parking_db;
USE parking_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    role ENUM('user', 'admin') DEFAULT 'user',
    notifications_active BOOLEAN DEFAULT TRUE,
    payment_preferences TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE parking_spaces (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(10) NOT NULL UNIQUE,
    type ENUM('standard', 'handicape', 'electrique') NOT NULL DEFAULT 'standard',
    status ENUM('libre', 'occupe', 'maintenance') NOT NULL DEFAULT 'libre',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    place_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    status ENUM('en_attente', 'confirmée', 'annulée') DEFAULT 'en_attente',
    code_acces VARCHAR(10),
    notification_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (place_id) REFERENCES parking_spaces(id)
);

CREATE TABLE tarifs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_place ENUM('standard', 'handicape', 'electrique'),
    prix_heure DECIMAL(10,2) NOT NULL,
    prix_journee DECIMAL(10,2) NOT NULL,
    prix_mois DECIMAL(10,2) NOT NULL
);

CREATE TABLE paiements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reservation_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    mode_paiement ENUM('carte', 'paypal', 'virement'),
    status ENUM('en_attente', 'valide', 'refuse', 'annule') DEFAULT 'en_attente',
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

CREATE TABLE factures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    paiement_id INT NOT NULL,
    numero_facture VARCHAR(20) NOT NULL UNIQUE,
    chemin_pdf VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paiement_id) REFERENCES paiements(id)
);

CREATE TABLE remboursements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    paiement_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    raison TEXT,
    status ENUM('en_cours', 'effectué', 'refusé') DEFAULT 'en_cours',
    commentaire_admin TEXT,
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paiement_id) REFERENCES paiements(id)
);

CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    titre VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('reservation', 'paiement', 'rappel', 'system') NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE horaires_ouverture (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jour_semaine INT NOT NULL, -- 1=Lundi, 2=Mardi, etc.
    heure_ouverture TIME NOT NULL,
    heure_fermeture TIME NOT NULL
);

-- Table pour les alertes de disponibilité
CREATE TABLE alertes_disponibilite (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    place_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    statut ENUM('en_attente', 'notifiee', 'expiree') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (place_id) REFERENCES parking_spaces(id)
);

-- Insertion des tarifs par défaut
INSERT INTO tarifs (type_place, prix_heure, prix_journee, prix_mois) VALUES
('standard', 2.00, 20.00, 200.00),
('handicape', 1.50, 15.00, 150.00),
('electrique', 3.00, 25.00, 250.00);

-- Insertion des horaires par défaut
INSERT INTO horaires_ouverture (jour_semaine, heure_ouverture, heure_fermeture) VALUES
(1, '08:00', '20:00'), -- Lundi
(2, '08:00', '20:00'), -- Mardi
(3, '08:00', '20:00'), -- Mercredi
(4, '08:00', '20:00'), -- Jeudi
(5, '08:00', '22:00'), -- Vendredi
(6, '09:00', '22:00'), -- Samedi
(7, '09:00', '20:00'); -- Dimanche
