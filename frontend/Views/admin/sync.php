<?php $pageTitle = 'Synchronisation des données - Administration Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h1 class="h3 mb-0">Synchronisation des données</h1>
        </div>
        <div class="card-body">
            <?php if (isset($syncResults)): ?>
                <div class="alert alert-info">
                    <h5>Résultats de la synchronisation</h5>
                    <ul>
                        <li><?= $syncResults['updated'] ?> places mises à jour</li>
                        <li><?= $syncResults['already_correct'] ?> places déjà correctes</li>
                        <li><?= $syncResults['errors'] ?> erreurs rencontrées</li>
                    </ul>
                </div>
                
                <?php if ($completedReservations > 0): ?>
                    <div class="alert alert-success">
                        <?= $completedReservations ?> réservations marquées comme terminées.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <p class="mb-4">Cette page permet de synchroniser les statuts des places de parking avec les réservations en cours, et de mettre à jour le statut des réservations terminées.</p>
            
            <form method="POST" action="<?= BASE_URL ?>/?page=admin&action=sync">
                <div class="d-grid gap-2 d-md-flex">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-2"></i> Lancer la synchronisation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
