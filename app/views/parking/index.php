<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Parkings disponibles</h1>
    
    <?php if (isset($parkings) && !empty($parkings)): ?>
        <div class="row">
            <?php foreach ($parkings as $parking): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($parking['nom']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($parking['adresse']) ?></h6>
                            <p class="card-text">
                                <strong>Tarif:</strong> <?= number_format($parking['tarif_horaire'], 2) ?> €/heure<br>
                                <strong>Places disponibles:</strong> <?= $parking['places_disponibles'] ?? 'Non disponible' ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="index.php?controller=parking&action=view&id=<?= $parking['id'] ?>" class="btn btn-info btn-sm">Détails</a>
                            <a href="index.php?controller=reservation&action=create&parking_id=<?= $parking['id'] ?>" class="btn btn-primary btn-sm">Réserver</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Aucun parking disponible pour le moment.
        </div>
    <?php endif; ?>
</div>
