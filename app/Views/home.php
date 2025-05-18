<?php require_once 'app/Views/layouts/header.php'; ?>

<h1>Bienvenue sur Parking App</h1>

<div class="home-stats">
    <div class="stat-card">
        <h3>Places disponibles</h3>
        <p class="stat-number"><?= $stats['places_libres'] ?>/<?= $stats['places_totales'] ?></p>
    </div>
</div>

<div class="features">
    <div class="feature-card">
        <h3>Réservez facilement</h3>
        <p>Trouvez et réservez une place de parking en quelques clics.</p>
        <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn-primary">Voir les places</a>
    </div>
    
    <div class="feature-card">
        <h3>Gérez vos réservations</h3>
        <p>Consultez et modifiez vos réservations en cours.</p>
        <a href="<?= BASE_URL ?>/?page=user&action=reservations" class="btn-primary">Mes réservations</a>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>
