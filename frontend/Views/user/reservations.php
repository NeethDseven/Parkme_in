<?php $pageTitle = 'Mes réservations'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">Mes réservations</h1>
    
    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Vous n'avez aucune réservation. <a href="<?= BASE_URL ?>/?page=parking&action=list" class="alert-link">Cliquez ici</a> pour réserver une place de parking.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">Vos réservations</h5>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Nouvelle réservation
                        </a>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover reservation-table">
                    <thead>
                        <tr>
                            <th>Place</th>
                            <th>Type</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $reservation): ?>
                            <tr class="<?= $reservation['status'] === 'annulée' ? 'table-secondary' : ($reservation['status'] === 'en_attente' ? 'table-warning' : '') ?>">
                                <td><?= $reservation['place_numero'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $reservation['place_type'] === 'standard' ? 'primary' : ($reservation['place_type'] === 'handicape' ? 'success' : 'warning') ?>">
                                        <?= ucfirst($reservation['place_type']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $reservation['status'] === 'confirmée' ? 'success' : ($reservation['status'] === 'en_attente' ? 'warning' : 'secondary') ?>">
                                        <?= $reservation['status'] === 'en_attente' ? 'En attente de paiement' : ucfirst($reservation['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($reservation['status'] === 'en_attente'): ?>
                                        <div class="btn-group">
                                            <a href="<?= BASE_URL ?>/?page=user&action=payment&reservation_id=<?= $reservation['id'] ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-credit-card me-1"></i> Payer
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $reservation['id'] ?>">
                                                <i class="fas fa-times me-1"></i> Annuler
                                            </a>
                                        </div>
                                    <?php elseif($reservation['status'] === 'confirmée' && strtotime($reservation['date_debut']) > time()): ?>
                                        <a href="#" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $reservation['id'] ?>">
                                            <i class="fas fa-times me-1"></i> Annuler
                                        </a>
                                        
                                        <a href="<?= BASE_URL ?>/?page=user&action=downloadReceipt&id=<?= $reservation['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                            <i class="fas fa-file-alt me-1"></i> Reçu
                                        </a>
                                    <?php elseif($reservation['status'] === 'confirmée'): ?>
                                        <a href="<?= BASE_URL ?>/?page=user&action=downloadReceipt&id=<?= $reservation['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                            <i class="fas fa-file-alt me-1"></i> Reçu
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            
                            <!-- Modal d'annulation -->
                            <div class="modal fade" id="cancelModal<?= $reservation['id'] ?>" tabindex="-1" aria-labelledby="cancelModalLabel<?= $reservation['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="cancelModalLabel<?= $reservation['id'] ?>">Confirmer l'annulation</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Êtes-vous sûr de vouloir annuler votre réservation pour la place n°<?= $reservation['place_numero'] ?> du <?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?> au <?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?> ?</p>
                                            <?php if($reservation['status'] === 'en_attente'): ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Cette réservation n'a pas encore été payée et sera simplement supprimée.
                                                </div>
                                            <?php else: ?>
                                                <p class="text-danger">Cette action ne peut pas être annulée. Un remboursement sera initié si applicable.</p>
                                            <?php endif; ?>
                                            <div class="alert alert-success">
                                                <i class="fas fa-bell me-2"></i>
                                                Les utilisateurs qui ont configuré des alertes pour ce créneau horaire seront automatiquement notifiés de la disponibilité.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                            <a href="<?= BASE_URL ?>/?page=user&action=cancelReservation&id=<?= $reservation['id'] ?>" class="btn btn-danger">Annuler la réservation</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .reservation-table .badge {
        font-size: 0.85rem;
    }
    
    .table-warning {
        --bs-table-accent-bg: rgba(255, 193, 7, 0.15);
    }
    
    .btn-group .btn {
        margin-right: 5px;
    }
</style>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
