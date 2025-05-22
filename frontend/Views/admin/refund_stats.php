<?php $pageTitle = 'Statistiques de remboursements'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Statistiques des remboursements</h1>
        <a href="<?= BASE_URL ?>/?page=admin&action=refunds" class="btn btn-primary">
            <i class="fas fa-list me-2"></i>Gérer les remboursements
        </a>
    </div>
    
    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Total des remboursements</h5>
                    <div class="display-4 text-danger">
                        <?= array_sum(array_column($monthlyStats, 'total_demandes')) ?>
                    </div>
                    <p class="card-text text-muted">Toutes périodes confondues</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Montant total remboursé</h5>
                    <div class="display-4 text-success">
                        <?= number_format(array_sum(array_column($monthlyStats, 'montant_total')), 2) ?> €
                    </div>
                    <p class="card-text text-muted">Toutes périodes confondues</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Taux d'acceptation</h5>
                    <?php 
                    $totalDemandes = array_sum(array_column($monthlyStats, 'total_demandes'));
                    $totalAcceptes = array_sum(array_column($monthlyStats, 'acceptes'));
                    $tauxAcceptation = $totalDemandes > 0 ? round(($totalAcceptes / $totalDemandes) * 100) : 0;
                    ?>
                    <div class="display-4 text-primary">
                        <?= $tauxAcceptation ?>%
                    </div>
                    <p class="card-text text-muted">Pourcentage de demandes acceptées</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphiques -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Évolution mensuelle des remboursements</h5>
                </div>
                <div class="card-body">
                    <canvas id="remboursements-chart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistiques détaillées -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Détails mensuels</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Mois</th>
                                    <th>Demandes</th>
                                    <th>Acceptées</th>
                                    <th>Refusées</th>
                                    <th>En cours</th>
                                    <th>Montant total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthlyStats as $stat): ?>
                                <tr>
                                    <td><?= $stat['mois'] ?></td>
                                    <td><?= $stat['total_demandes'] ?></td>
                                    <td><?= $stat['acceptes'] ?></td>
                                    <td><?= $stat['refuses'] ?></td>
                                    <td><?= $stat['en_cours'] ?></td>
                                    <td><?= number_format($stat['montant_total'], 2) ?> €</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Statistiques par motif</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Motif</th>
                                    <th>Nombre</th>
                                    <th>Acceptées</th>
                                    <th>Montant moyen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reasonStats as $stat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($stat['raison']) ?></td>
                                    <td><?= $stat['nombre'] ?></td>
                                    <td><?= $stat['acceptes'] ?></td>
                                    <td><?= number_format($stat['montant_moyen'], 2) ?> €</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Données pour les graphiques
const chartData = {
    months: <?= json_encode(array_reverse($chartData['months'])) ?>,
    counts: <?= json_encode(array_reverse($chartData['counts'])) ?>,
    amounts: <?= json_encode(array_reverse($chartData['amounts'])) ?>
};
</script>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
