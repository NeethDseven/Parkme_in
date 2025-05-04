-- Base de données: `parkme_in`
-- Version: 1.0
-- Date: 2023-11-28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS `parkme_in` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `parkme_in`;

-- Structure de la table `utilisateurs`
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structure de la table `parkings`
CREATE TABLE IF NOT EXISTS `parkings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `code_postal` varchar(10) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `capacite` int(11) NOT NULL,
  `tarif_horaire` decimal(5,2) NOT NULL,
  `ouverture` time NOT NULL,
  `fermeture` time NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structure de la table `places_parking`
CREATE TABLE IF NOT EXISTS `places_parking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parking_id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `type` enum('normale','handicapee','reservee') NOT NULL DEFAULT 'normale',
  `statut` enum('libre','occupee','hors_service') NOT NULL DEFAULT 'libre',
  PRIMARY KEY (`id`),
  KEY `parking_id` (`parking_id`),
  CONSTRAINT `fk_places_parking` FOREIGN KEY (`parking_id`) REFERENCES `parkings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structure de la table `reservations`
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `emplacement_id` int(11) NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `date_reservation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('en_attente','confirmée','terminée','annulée') NOT NULL DEFAULT 'en_attente',
  `prix` decimal(10,2) NOT NULL,
  `code_acces` varchar(10) DEFAULT NULL,
  `vehicule` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `emplacement_id` (`emplacement_id`),
  CONSTRAINT `fk_reservations_utilisateurs` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `fk_reservations_places` FOREIGN KEY (`emplacement_id`) REFERENCES `places_parking` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structure de la table `paiements`
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `methode` varchar(20) NOT NULL,
  `statut` enum('en_attente','complete','echec','annule') NOT NULL DEFAULT 'en_attente',
  `transaction_id` varchar(100) DEFAULT NULL,
  `date_paiement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `fk_paiements_reservations` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_paiements_utilisateurs` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structure de la table `notifications`
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'info',
  `lu` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `fk_notifications_utilisateurs` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
