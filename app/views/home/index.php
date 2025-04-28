<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="jumbotron">
    <div class="container">
        <h1 class="display-4">Bienvenue sur ParkMeIn</h1>
        <p class="lead">La solution simple et rapide pour réserver votre place de parking.</p>
        
        <?php if (!$isLoggedIn): ?>
            <hr class="my-4">
            <p>Créez un compte ou connectez-vous pour réserver votre place dès maintenant.</p>
            <a class="btn btn-primary btn-lg" href="index.php?controller=auth&action=register" role="button">Créer un compte</a>
            <a class="btn btn-outline-primary btn-lg" href="index.php?controller=auth&action=login" role="button">Se connecter</a>
        <?php else: ?>
            <a class="btn btn-primary btn-lg" href="index.php?controller=parking&action=index" role="button">Voir les parkings disponibles</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <?php if (!empty($featuredParkings)): ?>
        <h2 class="mb-4">Parkings populaires</h2>
        
        <div class="row">
            <?php foreach ($featuredParkings as $parking): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($parking['nom']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($parking['adresse']) ?></h6>
                            <p class="card-text">
                                <strong>Tarif:</strong> <?= number_format($parking['tarif_horaire'], 2) ?> €/heure
                            </p>
                            <a href="index.php?controller=parking&action=view&id=<?= $parking['id'] ?>" class="btn btn-sm btn-primary">Réserver</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="row mt-5">
        <div class="col-md-4">
            <h3>Rapide</h3>
            <p>Réservez votre place en quelques clics seulement, sans attente.</p>
        </div>
        <div class="col-md-4">
            <h3>Sécurisé</h3>
            <p>Nos parkings sont surveillés et accessibles uniquement avec votre code de réservation.</p>
        </div>
        <div class="col-md-4">
            <h3>Économique</h3>
            <p>Bénéficiez des meilleurs tarifs grâce à nos partenariats exclusifs avec les parkings.</p>
        </div>
    </div>
</div>
