<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkMeIn - Réservation de Parking</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="index.php">ParkMeIn</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=parking&action=index">Parkings</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=reservation&action=index">Mes réservations</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=reservation&action=history">Historique</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=user&action=profile">Mon profil</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=auth&action=logout">Déconnexion</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=auth&action=login">Connexion</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?controller=auth&action=register">Inscription</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container py-4">
