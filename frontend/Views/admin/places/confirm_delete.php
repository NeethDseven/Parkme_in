<?php $pageTitle = 'Confirmation de suppression - Administration Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h1 class="h3 mb-0">Confirmation de suppression</h1>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning">
                    <?= $_SESSION['warning'] ?>
                </div>
                <?php unset($_SESSION['warning']); ?>
            <?php endif; ?>
            
            <?php if ($place): ?>
                <div class="alert alert-danger">
                    <p class="fw-bold">Vous êtes sur le point de supprimer la place n°<?= htmlspecialchars($place['numero']) ?> (<?= htmlspecialchars($place['type']) ?>).</p>
                    <p>Cette action supprimera également toutes les réservations associées listées ci-dessous.</p>
                </div>
                
                <?php if (!empty($reservations)): ?>
                    <h5 class="mb-3">Réservations associées (<?= count($reservations) ?>)</h5>
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
                                <tr>
                                    <td><?= htmlspecialchars($reservation['numero']) ?></td>
                                    <td><?= htmlspecialchars($reservation['type']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></td>
                                    <td>
                                        <span class="badge <?= $reservation['status'] === 'confirmée' ? 'bg-success' : ($reservation['status'] === 'en_attente' ? 'bg-warning' : 'bg-secondary') ?>">
                                            <?= htmlspecialchars($reservation['status'] ?: 'annulée') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 d-flex gap-2">
                        <a href="<?= BASE_URL ?>/?page=admin&action=deletePlace&id=<?= $id ?>&force=1" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Confirmer la suppression
                        </a>
                        <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Annuler
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>Aucune réservation n'est associée à cette place.</p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Retour à la liste
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-danger">
                    <p>Place non trouvée.</p>
                </div>
                
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Retour à la liste
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// Nettoyer les variables de session après affichage
unset($_SESSION['reservations_to_delete']);
unset($_SESSION['place_to_delete']);
?>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
