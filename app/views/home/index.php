<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container mt-4">
    <!-- Hero Section -->
    <div class="jumbotron bg-light p-4 mb-5 rounded shadow-sm">
        <div class="container">
            <h1 class="display-4">Bienvenue sur ParkMeIn</h1>
            <p class="lead">Trouvez, réservez et gérez vos places de parking en toute simplicité.</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="mt-4">
                    <a href="index.php?controller=auth&action=login" class="btn btn-primary me-2">Se connecter</a>
                    <a href="index.php?controller=auth&action=register" class="btn btn-outline-primary">S'inscrire gratuitement</a>
                </div>
            <?php else: ?>
                <div class="mt-4">
                    <a href="index.php?controller=reservation&action=create" class="btn btn-success me-2">
                        <i class="bi bi-calendar-plus"></i> Nouvelle réservation
                    </a>
                    <a href="index.php?controller=reservation&action=index" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-check"></i> Mes réservations
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Features Section -->
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm fade-in">
                <div class="card-body text-center">
                    <i class="bi bi-search fs-1 text-primary mb-3"></i>
                    <h3 class="card-title">Trouvez</h3>
                    <p class="card-text">Recherchez et trouvez les places de parking disponibles près de votre destination.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm fade-in">
                <div class="card-body text-center">
                    <i class="bi bi-calendar2-check fs-1 text-primary mb-3"></i>
                    <h3 class="card-title">Réservez</h3>
                    <p class="card-text">Réservez votre place de parking en quelques clics et sécurisez votre emplacement.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm fade-in">
                <div class="card-body text-center">
                    <i class="bi bi-credit-card fs-1 text-primary mb-3"></i>
                    <h3 class="card-title">Payez</h3>
                    <p class="card-text">Payez en toute sécurité et gérez vos réservations depuis votre espace personnel.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Parking Section -->
    <?php if (!empty($parkings)): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="section-title mb-4">Parkings disponibles</h2>
            <div class="row">
                <?php foreach ($parkings as $parking): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm-hover h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 mb-0"><?= htmlspecialchars($parking['nom']) ?></h3>
                                <?php 
                                    $placesDisponibles = isset($parking['places_disponibles']) ? (int)$parking['places_disponibles'] : 0;
                                    $badgeClass = $placesDisponibles > 0 ? 'bg-success' : 'bg-danger';
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= $placesDisponibles ?> place<?= $placesDisponibles !== 1 ? 's' : '' ?> disponible<?= $placesDisponibles !== 1 ? 's' : '' ?>
                                </span>
                            </div>
                            <p class="text-muted mb-3">
                                <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($parking['adresse']) ?>, 
                                <?= htmlspecialchars($parking['code_postal']) ?> <?= htmlspecialchars($parking['ville']) ?>
                            </p>
                            <p class="mb-2">
                                <strong>Tarif:</strong> <?= number_format($parking['tarif_horaire'], 2, ',', ' ') ?> €/heure
                            </p>
                            <p class="mb-3">
                                <strong>Horaires:</strong> <?= substr($parking['ouverture'], 0, 5) ?> - <?= substr($parking['fermeture'], 0, 5) ?>
                            </p>
                            <?php if (!empty($parking['description'])): ?>
                            <p class="small text-muted"><?= htmlspecialchars($parking['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">
                                    <i class="bi bi-p-square"></i> 
                                    <?= isset($parking['places_disponibles']) && isset($parking['places_totales']) 
                                        ? $parking['places_disponibles'] . ' / ' . $parking['places_totales'] . ' places libres'
                                        : 'Places non disponibles' ?>
                                </span>
                                <a href="index.php?controller=reservation&action=create&parking_id=<?= $parking['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm"
                                   <?= $placesDisponibles <= 0 ? 'disabled' : '' ?>>
                                    <i class="bi bi-calendar-plus me-1"></i> Réserver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- How It Works Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="section-title mb-4">Comment ça marche ?</h2>
        </div>
        <div class="col-md-3">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <h3 class="h2 mb-0 text-primary">1</h3>
                    </div>
                    <h4 class="h5">Créez un compte</h4>
                    <p class="small text-muted">Inscrivez-vous gratuitement en quelques étapes simples</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <h3 class="h2 mb-0 text-primary">2</h3>
                    </div>
                    <h4 class="h5">Trouvez un parking</h4>
                    <p class="small text-muted">Consultez les disponibilités des parkings près de vous</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <h3 class="h2 mb-0 text-primary">3</h3>
                    </div>
                    <h4 class="h5">Réservez votre place</h4>
                    <p class="small text-muted">Choisissez votre créneau horaire et réservez en ligne</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <h3 class="h2 mb-0 text-primary">4</h3>
                    </div>
                    <h4 class="h5">Stationnez sereinement</h4>
                    <p class="small text-muted">Présentez votre code d'accès et profitez de votre emplacement</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
