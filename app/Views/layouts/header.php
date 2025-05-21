<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Parkme In'; ?></title>
    
    <!-- Bootstrap CSS avec lien direct -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    
    <!-- Google Fonts - Utilisation de Inter pour un style sobre et moderne -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- CSS personnalisé avec chemin absolu -->
    <link rel="stylesheet" href="/Projet/Parking%20final/public/css/style.css">
    
    <?php if (isset($extraCSS)): ?>
        <?php foreach($extraCSS as $css): ?>
            <link rel="stylesheet" href="/Projet/Parking%20final/public/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="/Projet/Parking%20final/">
                        <h1>Parkme In</h1>
                    </a>
                </div>
                <nav>
                    <ul>
                        <li><a href="/Projet/Parking%20final/"><i class="fas fa-home me-1"></i> Accueil</a></li>
                        <li><a href="/Projet/Parking%20final/?page=parking&action=list"><i class="fas fa-parking me-1"></i> Places</a></li>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="/Projet/Parking%20final/?page=user&action=reservations"><i class="fas fa-calendar-alt me-1"></i> Réservations</a></li>
                            <li>
                                <a href="/Projet/Parking%20final/?page=user&action=notifications" class="position-relative">
                                    <i class="fas fa-bell me-1"></i> Notifications
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
                                <li><a href="/Projet/Parking%20final/?page=admin"><i class="fas fa-tools me-1"></i> Admin</a></li>
                            <?php endif; ?>
                            
                            <li><a href="/Projet/Parking%20final/?page=logout"><i class="fas fa-sign-out-alt me-1"></i> Déconnexion</a></li>
                        <?php else: ?>
                            <li><a href="/Projet/Parking%20final/?page=login"><i class="fas fa-sign-in-alt me-1"></i> Connexion</a></li>
                            <li><a href="/Projet/Parking%20final/?page=register"><i class="fas fa-user-plus me-1"></i> Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="container main-content py-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
