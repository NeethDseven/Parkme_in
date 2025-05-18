<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Parking</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="<?= BASE_URL ?>/">
                    <h1>Parking App</h1>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="<?= BASE_URL ?>/">Accueil</a></li>
                    <li><a href="<?= BASE_URL ?>/?page=parking&action=list">Places disponibles</a></li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?= BASE_URL ?>/?page=user&action=reservations">Mes réservations</a></li>
                        <li>
                            <a href="<?= BASE_URL ?>/?page=user&action=notifications">
                                Notifications
                                <?php 
                                require_once 'app/Services/NotificationService.php';
                                $notificationService = new NotificationService();
                                $unreadCount = $notificationService->getUnreadCount($_SESSION['user_id']);
                                if ($unreadCount > 0):
                                ?>
                                <span class="notification-badge"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <li><a href="<?= BASE_URL ?>/?page=admin">Administration</a></li>
                        <?php endif; ?>
                        
                        <li><a href="<?= BASE_URL ?>/?page=logout">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/?page=login">Connexion</a></li>
                        <li><a href="<?= BASE_URL ?>/?page=register">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container main-content">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
