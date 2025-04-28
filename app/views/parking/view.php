<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container">
    <?php if (isset($parking) && $parking): ?>
        <h1 class="mb-3"><?= htmlspecialchars($parking['nom']) ?></h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Informations</h5>
                <p>
                    <strong>Adresse:</strong> <?= htmlspecialchars($parking['adresse']) ?><br>
                    <strong>Tarif horaire:</strong> <?= number_format($parking['tarif_horaire'], 2) ?> €<br>
                    <strong>Heures d'ouverture:</strong> <?= htmlspecialchars($parking['horaires'] ?? '24h/24') ?>
                </p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Places disponibles</h5>
                <a href="index.php?controller=reservation&action=create&parking_id=<?= $parking['id'] ?>" class="btn btn-primary">Réserver une place</a>
            </div>
            <div class="card-body">
                <?php if (isset($availableSpots) && !empty($availableSpots)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Type</th>
                                    <th>Étage</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availableSpots as $spot): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($spot['numero']) ?></td>
                                        <td><?= htmlspecialchars($spot['type']) ?></td>
                                        <td><?= htmlspecialchars($spot['etage'] ?? 'RDC') ?></td>
                                        <td>
                                            <a href="index.php?controller=reservation&action=create&parking_id=<?= $parking['id'] ?>&spot_id=<?= $spot['id'] ?>" class="btn btn-sm btn-primary">Réserver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Aucune place disponible actuellement.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="my-3">
            <a href="index.php?controller=parking&action=index" class="btn btn-secondary">Retour à la liste des parkings</a>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            Parking non trouvé.
        </div>
        <a href="index.php?controller=parking&action=index" class="btn btn-secondary">Retour à la liste des parkings</a>
    <?php endif; ?>
</div>
