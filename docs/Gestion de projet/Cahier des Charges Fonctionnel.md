# Cahier des Charges Fonctionnel (CDCF)

## Informations générales
- **Projet** : Application de gestion d'un parking
- **Nom du projet** : ParkMeIn
- **Date** : 15/04/2023
- **Version** : 1.0

## 1. Objectifs du projet

Ce projet vise à développer une application web complète permettant la gestion d'un parking. L'objectif est de fournir une interface utilisateur intuitive et responsive pour :

- Réserver des places de parking
- Consulter les disponibilités en temps réel
- Gérer son profil utilisateur et ses réservations
- Effectuer des paiements sécurisés

Le tout en assurant un système d'administration pour gérer les utilisateurs, les places, les tarifs et le suivi des paiements.

## 2. Acteurs du système

| Acteur | Rôle |
|--------|------|
| Visiteur | Peut s'inscrire et se connecter |
| Utilisateur | Réserve une place, paie, gère ses réservations et ses informations |
| Administrateur | Gère les utilisateurs, les places de parking, les tarifs, les horaires |

## 3. Fonctionnalités détaillées

### 3.1. Authentification

- Inscription avec nom, prénom, email, mot de passe, téléphone
- Connexion sécurisée
- Gestion de session
- L'administrateur peut activer, désactiver ou supprimer un compte utilisateur

### 3.2. Gestion des places de parking

- Création, modification et suppression de places de parking
- Statut de chaque place (occupée, libre)
- Typologie des places : normale, handicapée, réservée
- Tarification selon le moment : jour/nuit/week-end

### 3.3. Réservation

- Visualisation des disponibilités en temps réel
- Réservation à l'avance via calendrier
- Durée et horaire définis selon les règles fixées
- Modification ou annulation de réservation

### 3.4. Paiement

- Paiement en ligne (simulation par carte bancaire / PayPal)
- Calcul automatique du tarif selon les horaires et la durée
- Récupération de l'historique de paiements par l'utilisateur et l'admin

### 3.5. Notifications

- Rappel de réservation par e-mail ou notification système
- Alertes en cas de modification ou libération d'une place

### 3.6. Tableau de bord

- Utilisateur : aperçu des réservations passées/futures, gestion du profil
- Administrateur : vue d'ensemble sur les utilisateurs, les places, les réservations et les revenus

### 3.7. Gestion du profil

- Mise à jour des informations personnelles
- Historique des réservations
- Préférences de notification et de paiement

## 4. Contraintes et règles métiers

- Une place ne peut pas être réservée par plusieurs utilisateurs au même moment
- Les réservations doivent respecter les horaires définis par l'administrateur
- Une réservation doit être réglée pour être confirmée
- L'admin peut désactiver une réservation ou un utilisateur à tout moment

## 5. Interfaces attendues

**Interface utilisateur :**
- Accès aux réservations (création, modification, annulation)
- Paiements (historique, factures téléchargeables)
- Tableau de bord personnel (statistiques d'utilisation, réservations en cours)
- Carte interactive du parking avec visualisation des places disponibles
- Profil utilisateur avec gestion des préférences

**Interface admin :**
- Gestion utilisateurs (création, modification, suspension)
- Gestion des places (définition des zones, types de places, maintenance)
- Configuration des tarifs (par période, par type de place)
- Gestion des horaires d'ouverture
- Reporting et statistiques d'occupation
- Vue globale du parking en temps réel

**Interface mobile :**
- Responsive avec design fluide et accessible
- Optimisée pour les écrans tactiles
- Navigation simplifiée adaptée aux petits écrans
- Fonctionnalités essentielles accessibles en moins de 3 clics

## 6. Technologies utilisées

**Frontend :**
- HTML5, CSS3 (avec grille et flexbox)
- JavaScript (POO, MVC, sans framework)
- Validation des formulaires côté client
- Stockage local pour améliorer les performances

**Backend :**
- PHP 8.0+ (POO, MVC)
- API RESTful pour les communications client-serveur
- Sessions sécurisées avec CSRF protection

**Base de données :**
- MySQL 8.0+
- Transactions et contraintes d'intégrité
- Index optimisés pour les requêtes fréquentes

**Sécurité :**
- Hachage des mots de passe (Argon2id)
- Protection contre les injections SQL
- Validation et assainissement des données
- Journalisation des accès sensibles

**Autres contraintes :**
- Aucune bibliothèque externe autorisée
- Gestion d'erreurs et logs serveurs obligatoire
- Tests unitaires pour les fonctions critiques
- Documentation du code selon les standards PHPDoc

## 7. Données manipulées

| Entité | Champs principaux |
|--------|-------------------|
| Utilisateur | id, nom, prénom, email, téléphone, mot de passe, statut, rôle, date_inscription, derniere_connexion, preferences_notifications, avatar |
| Place | id, numéro, type, statut (occupée/libre), tarif_horaire, zone, étage, dimensions, restrictions, derniere_maintenance |
| Réservation | id, utilisateur_id, place_id, date_debut, date_fin, statut, montant_payé, code_accès, commentaires, récurrence, date_création |
| Paiement | id, utilisateur_id, reservation_id, montant, moyen_paiement, date, statut_transaction, référence_externe, details_facturation |
| Notification | id, utilisateur_id, type, message, statut_lu, date, priorité, canal_envoi, tentatives_envoi |
| Tarif | id, type_place, periode (jour/nuit/weekend), tarif_horaire, date_debut_validite, date_fin_validite, réduction_applicable, conditions_spéciales |
| Logs | id, date, utilisateur_id, action, ip, détails, gravité |

## 8. Éléments exclus (hors périmètre)

- Reconnaissance de plaque automatique
- Application native mobile
- Gestion de plusieurs parkings géographiquement séparés
- Intégration avec des systèmes externes de navigation GPS
- Gestion automatisée des barrières physiques
- Système de réservation d'autres services (recharge électrique, lavage)
- Paiements par cryptomonnaie
- Système de fidélité avec points/récompenses

## 9. Planning de livraison

Le projet sera développé selon une méthodologie Agile Scrum avec les étapes suivantes :

1. **Phase d'initialisation** : 12/04/2023 - 21/04/2023
   - Documentation et spécifications

2. **Phase de conception** : 22/04/2023 - 02/05/2023
   - Modélisation de données
   - Maquettes UI
   - Architecture technique

3. **Phase de développement** : 03/05/2023 - 05/06/2023
   - Sprint 1 : Environnement et authentification (03/05 - 12/05)
   - Sprint 2 : Gestion des places et réservations (13/05 - 22/05)
   - Sprint 3 : Paiement et notifications (23/05 - 01/06)
   - Sprint 4 : Tableau de bord et finalisations (02/06 - 05/06)

4. **Phase de tests** : 06/06/2023 - 15/06/2023
   - Tests unitaires et d'intégration
   - Correction de bugs

5. **Phase de déploiement** : 16/06/2023 - 23/06/2023
   - Préparation de l'environnement
   - Déploiement
   - Documentation et formation

## 10. Critères d'acceptation

Les critères d'acceptation généraux du projet :

- L'application doit être accessible via les navigateurs courants (Chrome, Firefox, Safari, Edge)
- Les temps de chargement des pages ne doivent pas excéder 2 secondes
- L'interface utilisateur doit être intuitive et responsive (PC, tablette, mobile)
- La sécurité des données personnelles doit être garantie
- Le système doit pouvoir gérer au minimum 1000 utilisateurs simultanément
- Les rapports administratifs doivent être précis et exportables

## 11. Annexes

- Maquettes de l'interface utilisateur (à fournir)
- Diagramme de la base de données (à fournir)
- Prototype fonctionnel (à fournir)
- Planning détaillé (voir diagramme de Gantt)
