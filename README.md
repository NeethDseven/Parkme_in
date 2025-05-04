# ParkMeIn - Application de Gestion de Parking

## À propos

ParkMeIn est une application web complète de gestion de parking permettant aux utilisateurs de rechercher, réserver et payer des places de stationnement en ligne. L'application offre également des fonctionnalités d'administration pour gérer les parkings, les places disponibles et les réservations.

## Fonctionnalités

### Pour les utilisateurs
- Inscription et connexion sécurisée
- Recherche et réservation de places de parking
- Paiement en ligne (simulation)
- Historique des réservations et des paiements
- Tableau de bord personnalisé
- Notifications en temps réel
- Impression des confirmations de réservation
- Gestion du profil utilisateur

### Pour les administrateurs
- Gestion des utilisateurs (ajout, modification, suppression)
- Gestion des parkings et des places
- Suivi des réservations et des paiements
- Tableau de bord avec statistiques
- Rapports sur les revenus et l'occupation

## Prérequis techniques

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx)
- Navigateur web moderne

## Installation

1. Cloner le dépôt
```bash
git clone https://github.com/username/parkme-in.git
```

2. Importer la base de données
```bash
mysql -u username -p < sql/database.sql
```

3. Configurer la connexion à la base de données
   - Modifier le fichier `app/models/Database.php` avec vos identifiants de base de données

4. Configurer le serveur web
   - Pointer le document root vers le dossier `public`
   - Activer le module de réécriture d'URL si nécessaire

5. Accéder à l'application
