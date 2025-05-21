# ParkMeIn - Application de gestion de parking

## Installation

1. Clonez ce dépôt dans votre répertoire web (ex: e:\xampp\htdocs\projet\Parkme_in)
2. Créez une base de données MySQL nommée `parking_db`
3. Importez le fichier `database.sql` dans votre base de données
4. Installez les dépendances :
   - Pour installer TCPDF (génération de PDF), exécutez : `php install_tcpdf.php`

## Structure des dossiers

- `backend/` : Contient les contrôleurs, modèles et services
- `frontend/` : Contient les vues
- `public/` : Fichiers publics (CSS, JS, images)
- `lib/` : Bibliothèques tierces

## Configuration

- La configuration de la base de données se trouve dans `backend/config/database.php`
- Les paramètres de base de l'application sont définis dans `index.php`

## Problèmes courants

### Erreur TCPDF

Si vous rencontrez une erreur avec TCPDF :
```
Warning: The use statement with non-compound name 'TCPDF' has no effect
```

Exécutez le script d'installation : `php install_tcpdf.php`

### Fichiers CSS/JS non chargés

Si les fichiers CSS et JS ne se chargent pas correctement, exécutez :
```
php move_files.php
```

Ce script copiera tous les fichiers statiques au bon endroit.
