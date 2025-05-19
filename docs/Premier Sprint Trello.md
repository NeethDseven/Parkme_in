# Premier Sprint Trello - Projet ParkMeIn

**Lien vers le tableau Trello:** https://trello.com/b/naeS92nm/gestion-de-projet

## Structure du tableau Trello du Sprint 1

### Colonnes
1. **Backlog Sprint** - Tâches sélectionnées pour le sprint
2. **À faire** - Tâches à démarrer
3. **En cours** - Tâches en cours de réalisation
4. **En test** - Tâches terminées en phase de test
5. **Terminé** - Tâches validées et terminées
6. **Bloqué** - Tâches qui rencontrent des obstacles

### User Stories du Sprint 1

#### Mise en place de l'environnement
- **Titre:** Initialisation du projet
- **Description:** Mettre en place l'architecture MVC et les environnements de développement
- **Critères d'acceptation:**
  - Structure de fichiers créée selon l'architecture MVC
  - Environnement local fonctionnel
  - Repository Git initialisé
- **Estimation:** 2 points
- **Assigné à:** Développeur Backend
- **Étiquettes:** Configuration, Priorité Haute

#### Structure de la base de données
- **Titre:** Création du schéma de base de données
- **Description:** Définir et créer toutes les tables nécessaires au projet
- **Critères d'acceptation:**
  - Toutes les tables définies dans le CDCF sont créées
  - Relations entre les tables établies
  - Script SQL prêt pour le déploiement
- **Estimation:** 5 points
- **Assigné à:** Développeur Backend
- **Étiquettes:** Base de données, Priorité Haute

#### Authentification - Inscription
- **Titre:** Développer le système d'inscription
- **Description:** Permettre aux utilisateurs de créer un compte
- **Critères d'acceptation:**
  - Formulaire d'inscription avec tous les champs requis
  - Validation des données côté client et serveur
  - Création du compte dans la base de données
  - Email de confirmation envoyé
- **Estimation:** 3 points
- **Assigné à:** Développeur Frontend / Backend
- **Étiquettes:** Authentification, Frontend, Backend

#### Authentification - Connexion
- **Titre:** Développer le système de connexion
- **Description:** Permettre aux utilisateurs de se connecter à leur compte
- **Critères d'acceptation:**
  - Formulaire de connexion
  - Validation des identifiants
  - Création et gestion de session
  - Système de récupération de mot de passe
- **Estimation:** 3 points
- **Assigné à:** Développeur Frontend / Backend
- **Étiquettes:** Authentification, Frontend, Backend

#### Authentification - Gestion des utilisateurs (Admin)
- **Titre:** Interface d'administration des utilisateurs
- **Description:** Permettre à l'administrateur de gérer les utilisateurs
- **Critères d'acceptation:**
  - Liste des utilisateurs avec filtres de recherche
  - Possibilité d'activer/désactiver un compte
  - Possibilité de supprimer un compte
  - Historique des actions sur les comptes
- **Estimation:** 5 points
- **Assigné à:** Développeur Backend
- **Étiquettes:** Administration, Backend

### Tâches techniques

1. **Configuration du serveur local**
   - Installation et configuration de l'environnement XAMPP
   - Configuration des droits d'accès
   - Configuration des variables d'environnement

2. **Création du routeur MVC**
   - Développement du système de routage
   - Mise en place de la structure Controllers/Models/Views

3. **Mise en place du système de logs**
   - Configuration du système de logs pour tracer les erreurs
   - Système de journalisation des activités utilisateurs

4. **Intégration des templates de base**
   - Création des templates HTML/CSS de base
   - Mise en place du système de templates réutilisables

5. **Tests unitaires des fonctions d'authentification**
   - Création des tests pour les fonctions d'inscription
   - Création des tests pour les fonctions de connexion
