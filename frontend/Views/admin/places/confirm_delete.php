<?php $pageTitle = 'Confirmation de suppression - Administration Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="card">
        <div class="card-header bg-warning text-white">
            <h3 class="card-title mb-0">Confirmation de suppression</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Vous êtes sur le point de supprimer la place n°<?= htmlspecialchars($place['numero']) ?> (<?= htmlspecialchars($place['type']) ?>).
            </div>
            
            <p class="mb-4">Cette action supprimera également toutes les réservations associées listées ci-dessous.</p>
            
            <?php 
                // Filtrer les réservations actives (non annulées)
                $activeReservations = array_filter($reservations, function($r) {
                    return $r['status'] !== 'annulée';
                });
                
                $showWarning = !empty($activeReservations);
            ?>
            
            <?php if ($showWarning): ?>
            <div class="alert alert-danger">
                <strong><i class="fas fa-exclamation-circle me-2"></i>Attention!</strong> 
                Cette place possède <?= count($activeReservations) ?> réservation(s) active(s) qui seront également supprimées.
            </div>
            <?php endif; ?>
            
            <h4>Réservations associées (<?= count($reservations) ?>)</h4>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Place</th>
                            <th>Type</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $reservation): ?>
                            <tr class="<?= $reservation['status'] === 'annulée' ? 'table-secondary' : 'table-warning' ?>">
                                <td><?= htmlspecialchars($reservation['numero']) ?></td>
                                <td><?= htmlspecialchars($reservation['type']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $reservation['status'] === 'annulée' ? 'secondary' : 'success' ?>">
                                        <?= htmlspecialchars($reservation['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Annuler
                </a>
                <a href="<?= BASE_URL ?>/?page=admin&action=deletePlace&id=<?= $id ?>&force=1" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-2"></i> Confirmer la suppression
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
