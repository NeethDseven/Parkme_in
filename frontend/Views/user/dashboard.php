<?php $pageTitle = 'Tableau de bord utilisateur - Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">Tableau de bord utilisateur</h1>
    
    <div class="row mb-4">
        <!-- Carte statistique - Réservations actives -->
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-2">
                        <span class="stat-counter" data-target="<?= $stats['reservations_actives'] ?>">
                            <?= $stats['reservations_actives'] ?>
                        </span>
                    </div>
                    <h5 class="card-title">Réservations actives</h5>
                    <p class="card-text text-muted">
                        <i class="fas fa-calendar-check me-1"></i> 
                        Vos réservations en cours
                    </p>
                    <a href="<?= BASE_URL ?>/?page=user&action=reservations" class="stretched-link"></a>
                </div>
            </div>
        </div>
        
        <!-- Carte statistique - Dépenses totales -->
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-success mb-2">
                        <span class="stat-counter" data-target="<?= $stats['total_depense'] ?>">
                            <?= $stats['total_depense'] ?>€
                        </span>
                    </div>
                    <h5 class="card-title">Total dépensé</h5>
                    <p class="card-text text-muted">
                        <i class="fas fa-euro-sign me-1"></i> 
                        Vos dépenses totales
                    </p>
                    <a href="<?= BASE_URL ?>/?page=user&action=history" class="stretched-link"></a>
                </div>
            </div>
        </div>
        
        <!-- Carte statistique - Prochaine réservation -->
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-center">Prochaine réservation</h5>
                    <?php if ($stats['prochaine_reservation']): ?>
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-3 text-primary">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold">
                                    Place n°<?= $stats['prochaine_reservation']['place_numero'] ?>
                                </span>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($stats['prochaine_reservation']['date_debut'])) ?>
                                </small>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <a href="<?= BASE_URL ?>/?page=user&action=reservations" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> Voir toutes
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-calendar-times text-muted fa-2x mb-2"></i>
                            <p class="mb-0">Aucune réservation à venir</p>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Réserver maintenant
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Actions rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn btn-outline-primary w-100 p-3">
                                <i class="fas fa-car fa-2x mb-2"></i><br>
                                Réserver une place
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="<?= BASE_URL ?>/?page=user&action=reservations" class="btn btn-outline-info w-100 p-3">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i><br>
                                Gérer mes réservations
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="<?= BASE_URL ?>/?page=user&action=history" class="btn btn-outline-success w-100 p-3">
                                <i class="fas fa-receipt fa-2x mb-2"></i><br>
                                Historique paiements
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="<?= BASE_URL ?>/?page=user&action=profile" class="btn btn-outline-secondary w-100 p-3">
                                <i class="fas fa-user-cog fa-2x mb-2"></i><br>
                                Mon profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphique d'historique des réservations -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Vos réservations - 30 derniers jours
                    </h5>
                    <a href="<?= BASE_URL ?>/?page=user&action=reservations" class="btn btn-sm btn-primary">
                        <i class="fas fa-list me-1"></i> Voir toutes vos réservations
                    </a>
                </div>
                <div class="card-body">
                    <div id="reservations-chart" style="height: 300px;">
                        <!-- Le graphique sera généré ici par JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script pour initialiser les graphiques -->
<script>
    // Données pour le graphique des réservations
    const reservationsData = <?= $jsData['occupationData'] ? json_encode($jsData['occupationData']) : '[]' ?>;
</script>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
