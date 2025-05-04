<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkMeIn - Réservation de Parking</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Style personnalisé -->
    <style>
        .fade-in {
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .shadow-sm-hover:hover {
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
            transition: box-shadow 0.3s ease-in-out;
        }
        .bg-light-custom {
            background-color: #f8f9fa;
        }
        .section-title {
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }
        .accent-border-left {
            border-left: 3px solid #007bff;
            padding-left: 15px;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.75em;
        }
    </style>
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
                            <!-- Notifications dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-bell-fill"></i>
                                    <span id="notification-badge" class="badge badge-danger notification-badge d-none">0</span>
                                </a>
                                <div id="notification-dropdown-menu" class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationsDropdown">
                                    <div class="dropdown-item text-center text-muted">Chargement...</div>
                                </div>
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
