<?php $pageTitle = 'Gestion des remboursements - Administration Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h1 class="h3 mb-0">Gestion des remboursements</h1>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (empty($remboursements)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucune demande de remboursement en attente.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date demande</th>
                                <th>Client</th>
                                <th>Réservation</th>
                                <th>Montant</th>
                                <th>Raison</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($remboursements as $remb): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($remb['date_demande'])) ?></td>
                                <td><?= htmlspecialchars($remb['nom']) ?> <?= htmlspecialchars($remb['prenom']) ?></td>
                                <td>
                                    <div><strong>Place <?= isset($remb['place_numero']) ? htmlspecialchars($remb['place_numero']) : 'N/A' ?></strong></div>
                                    <div class="text-muted small"><?= isset($remb['date_debut']) ? date('d/m/Y', strtotime($remb['date_debut'])) : 'N/A' ?></div>
                                </td>
                                <td><strong class="text-primary"><?= number_format($remb['montant'], 2) ?> €</strong></td>
                                <td><?= htmlspecialchars($remb['raison']) ?></td>
                                <td>
                                    <?php
                                    switch($remb['status']) {
                                        case 'en_cours':
                                            echo '<span class="badge bg-warning">En attente</span>';
                                            break;
                                        case 'accepte':
                                            echo '<span class="badge bg-success">Accepté</span>';
                                            break;
                                        case 'refuse':
                                            echo '<span class="badge bg-danger">Refusé</span>';
                                            break;
                                        default:
                                            echo htmlspecialchars($remb['status']);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if($remb['status'] === 'en_cours'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/?page=admin&action=processRefund" class="d-flex gap-1">
                                            <input type="hidden" name="remboursement_id" value="<?= $remb['id'] ?>">
                                            <button type="submit" name="decision" value="accepte" class="btn btn-sm btn-success">
                                                <i class="fas fa-check me-1"></i> Accepter
                                            </button>
                                            <button type="submit" name="decision" value="refuse" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times me-1"></i> Refuser
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
