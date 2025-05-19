<?php $pageTitle = 'Mes réservations - Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">Mes réservations</h1>
     <!-- Interface simplifiée sans onglets pour éliminer les problèmes potentiels -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Liste de vos réservations</h2>
        </div>
        <div class="card-body">
            <?php if (empty($reservations)): ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Vous n'avez aucune réservation.
                </div>
            <?php else: ?>
                <!-- Réservations actives -->
                <h3 class="h5 mb-3">Réservations actives</h3>
                <div class="table-responsive mb-4">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Place</th>
                                <th>Période</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $activeFound = false;
                            foreach($reservations as $reservation):
                                if ($reservation['status'] === 'confirmée' || $reservation['status'] === 'en_attente'):
                                    $activeFound = true;
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">N°<?= htmlspecialchars($reservation['place_numero']) ?></span>
                                        <small class="d-block mt-1"><?= ucfirst(htmlspecialchars($reservation['place_type'])) ?></small>
                                    </td>
                                    <td>
                                        <div>Du: <?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></div>
                                        <div>Au: <?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($reservation['status'] === 'confirmée'): ?>
                                            <span class="badge bg-success">Confirmée</span>
                                        <?php elseif ($reservation['status'] === 'en_attente'): ?>
                                            <span class="badge bg-warning">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="<?= BASE_URL ?>/?page=user&action=cancelReservation&id=<?= $reservation['id'] ?>" 
                                                class="btn btn-sm btn-danger">
                                                <i class="fas fa-times me-1"></i> Annuler
                                            </a>
                                            
                                            <?php if ($reservation['status'] === 'confirmée'): ?>
                                                <a href="<?= BASE_URL ?>/?page=user&action=downloadReceipt&id=<?= $reservation['id'] ?>" 
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($reservation['status'] === 'en_attente'): ?>
                                                <a href="<?= BASE_URL ?>/?page=user&action=payment&id=<?= $reservation['id'] ?>" 
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-credit-card me-1"></i> Payer
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endif;
                            endforeach;
                            
                            if (!$activeFound):
                            ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3">
                                        <p class="text-muted mb-0">Aucune réservation active trouvée.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Historique des réservations -->
                <h3 class="h5 mb-3">Historique des réservations</h3>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Place</th>
                                <th>Période</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $historyFound = false;
                            foreach($reservations as $reservation):
                                if ($reservation['status'] === 'annulée' || $reservation['status'] === 'terminée'):
                                    $historyFound = true;
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">N°<?= htmlspecialchars($reservation['place_numero']) ?></span>
                                        <small class="d-block mt-1"><?= ucfirst(htmlspecialchars($reservation['place_type'])) ?></small>
                                    </td>
                                    <td>
                                        <div>Du: <?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></div>
                                        <div>Au: <?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($reservation['status'] === 'annulée'): ?>
                                            <span class="badge bg-danger">Annulée</span>
                                        <?php elseif ($reservation['status'] === 'terminée'): ?>
                                            <span class="badge bg-secondary">Terminée</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                                endif;
                            endforeach;
                            
                            if (!$historyFound):
                            ?>
                                <tr>
                                    <td colspan="3" class="text-center py-3">
                                        <p class="text-muted mb-0">Aucun historique de réservation trouvé.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-white">
            <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Réserver une place
            </a>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>
