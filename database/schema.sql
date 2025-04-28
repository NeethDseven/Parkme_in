-- Database schema for ParkMeIn application

-- 1. Users table
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    role ENUM('admin', 'user') DEFAULT 'user',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Parking lots table
CREATE TABLE IF NOT EXISTS parkings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    code_postal VARCHAR(10) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    nb_places_total INT NOT NULL,
    tarif_horaire DECIMAL(10, 2) NOT NULL,
    ouverture TIME NOT NULL,
    fermeture TIME NOT NULL,
    description TEXT
);

-- 3. Parking spots table
CREATE TABLE IF NOT EXISTS places_parking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parking_id INT NOT NULL,
    numero VARCHAR(10) NOT NULL,
    numero_place VARCHAR(10) NOT NULL,
    type ENUM('normale', 'handicapée', 'réservée') DEFAULT 'normale',
    etage VARCHAR(10),
    statut ENUM('libre', 'occupée') DEFAULT 'libre',
    tarif_horaire DECIMAL(10, 2),
    FOREIGN KEY (parking_id) REFERENCES parkings(id) ON DELETE CASCADE
);

-- 4. Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    emplacement_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    date_reservation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    vehicule VARCHAR(100),
    statut ENUM('en attente', 'confirmée', 'annulée') DEFAULT 'en attente',
    prix DECIMAL(10, 2) NOT NULL,
    code_acces VARCHAR(20),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (emplacement_id) REFERENCES places_parking(id) ON DELETE CASCADE
);

-- 5. Payments table
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    methode ENUM('CB', 'PayPal', 'Espèces', 'Autre') NOT NULL,
    date_paiement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reference VARCHAR(100) UNIQUE,
    statut ENUM('payé', 'en attente', 'échoué') DEFAULT 'en attente',
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
);

-- 6. Reviews table
CREATE TABLE IF NOT EXISTS avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    parking_id INT NOT NULL,
    note TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    date_avis DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (parking_id) REFERENCES parkings(id) ON DELETE CASCADE
);

-- Create a view to show all available parking spots with their associated parking information
CREATE OR REPLACE VIEW vw_available_parking_spots AS
SELECT 
    pp.id as place_id,
    pp.numero,
    pp.numero_place,
    pp.type,
    pp.etage,
    pp.statut,
    COALESCE(pp.tarif_horaire, p.tarif_horaire) as tarif_horaire,
    p.id as parking_id,
    p.nom as parking_nom,
    p.adresse,
    p.code_postal,
    p.ville,
    p.latitude,
    p.longitude,
    p.nb_places_total,
    p.ouverture,
    p.fermeture,
    p.description
FROM 
    places_parking pp
JOIN 
    parkings p ON pp.parking_id = p.id
WHERE 
    pp.statut = 'libre';
