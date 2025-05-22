<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Parkme In - Gestion de parking' ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= PUBLIC_URL ?>/img/favicon.ico" type="image/x-icon">
    
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>/css/style.css">
    
    <!-- Scripts JS communs -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="<?= PUBLIC_URL ?>/js/app.js" defer></script>
    
    <?php if (isset($extraCSS) && is_array($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
        <link rel="stylesheet" href="<?= PUBLIC_URL ?>/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($extraJS) && is_array($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
        <script src="<?= PUBLIC_URL ?>/js/<?= $js ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= isset($_SESSION['user_id']) ? 'user-logged' : '' ?>">
    <!-- Navbar principale -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/">
                <i class="fas fa-parking me-2"></i>Parkme In
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?page=parking&action=list">Places disponibles</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/?page=user&action=reservations">Mes réservations</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Administration
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=admin&action=index">Tableau de bord</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=admin&action=places">Gestion des places</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=admin&action=users">Gestion des utilisateurs</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=admin&action=refunds">Remboursements</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=admin&action=tarifs">Tarifs</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="btn-group me-2">
                            <a href="<?= BASE_URL ?>/?page=user&action=notifications" class="btn btn-outline-light position-relative">
                                <i class="fas fa-bell"></i>
                                <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                                    0
                                </span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>Mon compte
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Connecté en tant que <?= $_SESSION['user_email'] ?? 'Utilisateur' ?></h6></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=user&action=dashboard"><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=user&action=reservations"><i class="fas fa-calendar-check me-2"></i>Mes réservations</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=user&action=history"><i class="fas fa-history me-2"></i>Historique de paiement</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=user&action=profile"><i class="fas fa-user-cog me-2"></i>Mon profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/?page=logout"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/?page=login" class="btn btn-outline-light me-2">
                            <i class="fas fa-sign-in-alt me-1"></i>Connexion
                        </a>
                        <a href="<?= BASE_URL ?>/?page=register" class="btn btn-light">
                            <i class="fas fa-user-plus me-1"></i>Inscription
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Conteneur principal -->
    <main>
