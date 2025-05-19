# Cahier des Charges Techniques (CDCT)

## Projet : ParkMeIn
**Date :** 13/06/2025  
**Version :** 1.0  
**Auteur :** Labidi Sami  
**Référence CDCF :** Version 1.0 du 16/05/2025

## 1. Introduction

Ce document technique définit l'architecture et les spécifications techniques de l'application web ParkMeIn basée sur les exigences établies dans le Cahier des Charges Fonctionnel (CDCF). Il s'adresse principalement aux développeurs et aux équipes techniques impliquées dans la réalisation du projet.

## 2. Architecture générale

### 2.1 Architecture MVC

L'application sera développée selon une architecture MVC (Modèle-Vue-Contrôleur) avec une séparation claire des responsabilités :

- **Modèle :** Gestion des données et de la logique métier
- **Vue :** Interface utilisateur et présentation des données
- **Contrôleur :** Traitement des actions utilisateur et coordination entre le modèle et la vue

### 2.2 Structure des dossiers

```
/
├── config/               # Configuration du projet
│   ├── database.php      # Configuration de la base de données
│   └── config.php        # Configuration générale
├── models/               # Modèles de l'application
├── views/                # Vues de l'application
│   ├── templates/        # Templates réutilisables
│   ├── admin/            # Vues pour l'administration
│   └── user/             # Vues pour l'utilisateur
├── controllers/          # Contrôleurs de l'application
├── public/               # Ressources accessibles publiquement
│   ├── css/              # Feuilles de style
│   ├── js/               # Scripts JavaScript
│   └── images/           # Images et ressources graphiques
├── utils/                # Utilitaires et classes communes
│   ├── Logger.php        # Système de logs
│   ├── Database.php      # Classe d'abstraction de la base de données
│   └── Authentication.php # Gestion de l'authentification
├── tests/                # Tests unitaires et fonctionnels
├── logs/                 # Fichiers de logs (non accessible via le web)
└── index.php             # Point d'entrée unique de l'application
```

## 3. Spécifications techniques

### 3.1 Environnement de développement

- **Serveur web :** Apache 2.4+
- **PHP :** Version 8.1+
- **Base de données :** MySQL 8.0+
- **Outils de développement :** Visual Studio Code, GitLab, Postman
- **Environnement local :** XAMPP

### 3.2 Frontend

#### 3.2.1 Technologies

- **HTML5** pour la structure des pages
- **CSS3** pour la mise en page et le style
- **JavaScript (ES6+)** pour l'interactivité côté client
- **Responsive Design** utilisant les media queries pour l'adaptation mobile

#### 3.2.2 Structure JavaScript (POO)

Le JavaScript suivra une approche orientée objet avec :

```javascript
// Exemple de structure
class Model {
    constructor() {
        // Initialisation
    }
    
    fetchData() {
        // Méthode pour récupérer les données
    }
}

class View {
    constructor() {
        // Initialisation
    }
    
    render(data) {
        // Méthode pour afficher les données
    }
}

class Controller {
    constructor(model, view) {
        this.model = model;
        this.view = view;
    }
    
    init() {
        // Initialisation du contrôleur
    }
}

// Initialisation de l'application
const app = new Controller(new Model(), new View());
app.init();
```

#### 3.2.3 Compatibilité navigateurs

- Chrome (dernières 2 versions majeures)
- Firefox (dernières 2 versions majeures)
- Safari (dernières 2 versions majeures)
- Edge (dernières 2 versions majeures)

### 3.3 Backend

#### 3.3.1 Structure PHP (POO)

Le PHP suivra une approche orientée objet avec :

```php
// Exemple de structure d'un contrôleur
class UserController {
    private $userModel;
    private $view;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->view = new View();
    }
    
    public function index() {
        $users = $this->userModel->getAll();
        $this->view->render('users/index', ['users' => $users]);
    }
    
    public function create($userData) {
        $result = $this->userModel->create($userData);
        if ($result) {
            // Traitement réussi
        } else {
            // Gestion des erreurs
        }
    }
}
```

#### 3.3.2 Base de données

Le schéma de base de données comprendra les tables suivantes :

**users**
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**places**
```sql
CREATE TABLE places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(10) NOT NULL UNIQUE,
    type ENUM('normal', 'handicap', 'reserved') DEFAULT 'normal',
    status ENUM('available', 'occupied') DEFAULT 'available',
    hourly_rate DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**reservations**
```sql
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    place_id INT NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
);
```

**payments**
```sql
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reservation_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('credit_card', 'paypal', 'other') NOT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
);
```

**notifications**
```sql
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('reservation', 'payment', 'reminder', 'system') NOT NULL,
    message TEXT NOT NULL,
    read_status BOOLEAN DEFAULT FALSE,
    notification_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**tarifs**
```sql
CREATE TABLE tarifs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    place_type ENUM('normal', 'handicap', 'reserved') NOT NULL,
    period ENUM('day', 'night', 'weekend') NOT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL,
    valid_from DATE NOT NULL,
    valid_to DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 3.3.3 Sécurité

- **Authentification :** Sessions PHP sécurisées avec hashage des mots de passe (Bcrypt)
- **Protection contre les injections SQL :** Requêtes préparées PDO
- **Protection CSRF :** Tokens de vérification pour les formulaires
- **XSS :** Échappement systématique des données affichées avec htmlspecialchars()
- **HTTPS :** Configuration du serveur pour HTTPS uniquement

## 4. APIs et Intégrations

### 4.1 API REST interne

Une API REST sera développée pour les échanges entre le frontend et le backend :

- **Authentification :** `/api/auth/login`, `/api/auth/register`
- **Utilisateurs :** `/api/users`, `/api/users/{id}`
- **Places :** `/api/places`, `/api/places/{id}`, `/api/places/available`
- **Réservations :** `/api/reservations`, `/api/reservations/{id}`
- **Paiements :** `/api/payments`, `/api/payments/{id}`

Format de réponse standard :
```json
{
  "success": true/false,
  "data": {}, // ou null en cas d'erreur
  "message": "Message explicatif", // principalement utilisé en cas d'erreur
  "errors": [] // détails des erreurs le cas échéant
}
```

### 4.2 Système de notification

- Notifications en temps réel utilisant XMLHttpRequest / Fetch API avec polling
- Notifications par email via PHPMailer

## 5. Tests et qualité

### 5.1 Tests unitaires

- Utilisation de PHPUnit pour les tests backend
- Tests JavaScript pour le frontend

### 5.2 Logs et monitoring

- Système de logs personnalisé avec différents niveaux (info, warning, error, critical)
- Journalisation des erreurs PHP et JavaScript
- Journalisation des actions importantes (connexions, paiements, réservations)

## 6. Déploiement

### 6.1 Prérequis serveur

- Serveur Apache ou Nginx
- PHP 8.1+ avec extensions requises (PDO, mysqli, mbstring, json)
- MySQL 8.0+
- 2GB RAM minimum
- 10GB espace disque minimum

### 6.2 Procédure de déploiement

1. Préparation de l'environnement de production
2. Configuration de la base de données
3. Upload des fichiers sources
4. Configuration du serveur web
5. Tests de validation post-déploiement

## 7. Performance et optimisation

- Minification des ressources CSS et JavaScript
- Compression Gzip activée
- Mise en cache des ressources statiques
- Optimisation des requêtes SQL avec indexation appropriée

## 8. Maintenance et évolutivité

- Documentation du code selon les standards PHPDoc
- Architecture modulaire pour faciliter les évolutions futures
- Versioning du code avec Git
- Procédures de sauvegarde régulières de la base de données

## 9. Validation

Ce CDCT a été validé le 17/05/2025 par :

- **Responsable technique :**

   Labidi Sami

 **Chef de projet :**  

  Labidi Sami
