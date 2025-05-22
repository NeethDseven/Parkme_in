<?php $pageTitle = 'Historique des paiements'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">Historique des paiements</h1>
    
    <?php if (empty($historique)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> Vous n'avez aucun paiement dans votre historique.
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <ul class="nav nav-pills card-header-pills" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab" aria-controls="payments" aria-selected="true">
                            <i class="fas fa-credit-card me-1"></i> Paiements
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="refunds-tab" data-bs-toggle="tab" data-bs-target="#refunds" type="button" role="tab" aria-controls="refunds" aria-selected="false">
                            <i class="fas fa-undo-alt me-1"></i> Remboursements
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Place</th>
                                        <th>Période</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($historique as $paiement): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($paiement['date_paiement'])) ?></td>
                                            <td>N°<?= $paiement['place_numero'] ?></td>
                                            <td class="small">
                                                <div><?= date('d/m/Y H:i', strtotime($paiement['date_debut'])) ?></div>
                                                <div>au</div>
                                                <div><?= date('d/m/Y H:i', strtotime($paiement['date_fin'])) ?></div>
                                            </td>
                                            <td><strong><?= number_format($paiement['montant'], 2) ?> €</strong></td>
                                            <td>
                                                <?php if($paiement['status'] === 'valide'): ?>
                                                    <span class="badge bg-success">Payé</span>
                                                <?php elseif($paiement['status'] === 'en_attente'): ?>
                                                    <span class="badge bg-warning text-dark">En attente</span>
                                                <?php elseif($paiement['status'] === 'annule'): ?>
                                                    <span class="badge bg-danger">Annulé</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Refusé</span>
                                                <?php endif; ?>
                                                
                                                <?php if(isset($paiement['remboursement_status'])): ?>
                                                    <?php if($paiement['remboursement_status'] === 'effectué'): ?>
                                                        <span class="badge bg-info">Remboursé</span>
                                                    <?php elseif($paiement['remboursement_status'] === 'en_cours'): ?>
                                                        <span class="badge bg-warning text-dark">Remboursement en cours</span>
                                                    <?php elseif($paiement['remboursement_status'] === 'refusé'): ?>
                                                        <span class="badge bg-danger">Remboursement refusé</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($paiement['status'] === 'valide' && empty($paiement['remboursement_status'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#refundModal<?= $paiement['id'] ?>">
                                                        <i class="fas fa-undo-alt me-1"></i> Demander un remboursement
                                                    </button>
                                                <?php elseif(isset($paiement['remboursement_status']) && $paiement['remboursement_status'] === 'refusé'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#refundInfoModal<?= $paiement['id'] ?>">
                                                        <i class="fas fa-info-circle me-1"></i> Détails du refus
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        
                                        <!-- Modal de demande de remboursement -->
                                        <?php if($paiement['status'] === 'valide' && empty($paiement['remboursement_status'])): ?>
                                            <div class="modal fade" id="refundModal<?= $paiement['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="<?= BASE_URL ?>/?page=user&action=refund" method="post">
                                                            <input type="hidden" name="paiement_id" value="<?= $paiement['id'] ?>">
                                                            
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">Demande de remboursement</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Vous êtes sur le point de demander le remboursement de <strong><?= number_format($paiement['montant'], 2) ?> €</strong> pour votre réservation du <?= date('d/m/Y H:i', strtotime($paiement['date_debut'])) ?> au <?= date('d/m/Y H:i', strtotime($paiement['date_fin'])) ?>.</p>
                                                                
                                                                <div class="form-group mb-3">
                                                                    <label for="raison<?= $paiement['id'] ?>" class="form-label">Raison du remboursement :</label>
                                                                    <textarea id="raison<?= $paiement['id'] ?>" name="raison" class="form-control" rows="3" required></textarea>
                                                                </div>
                                                                
                                                                <div class="alert alert-info">
                                                                    <i class="fas fa-info-circle me-2"></i> Votre demande sera examinée par un administrateur. Vous serez notifié de la décision.
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-primary">Envoyer la demande</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif(isset($paiement['remboursement_status']) && $paiement['remboursement_status'] === 'refusé'): ?>
                                            <!-- Modal d'information sur le refus -->
                                            <div class="modal fade" id="refundInfoModal<?= $paiement['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Détails du refus de remboursement</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Votre demande de remboursement de <strong><?= number_format($paiement['montant'], 2) ?> €</strong> pour la réservation du <?= date('d/m/Y H:i', strtotime($paiement['date_debut'])) ?> au <?= date('d/m/Y H:i', strtotime($paiement['date_fin'])) ?> a été refusée.</p>
                                                            
                                                            <?php if(!empty($paiement['commentaire_admin'])): ?>
                                                                <div class="alert alert-danger">
                                                                    <h6 class="mb-2">Motif du refus :</h6>
                                                                    <p class="mb-0"><?= htmlspecialchars($paiement['commentaire_admin']) ?></p>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <div class="alert alert-info">
                                                                <i class="fas fa-info-circle me-2"></i> Pour toute question concernant cette décision, veuillez contacter notre service client.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="refunds" role="tabpanel" aria-labelledby="refunds-tab">
                        <!-- Filtrer seulement les paiements avec un remboursement -->
                        <?php 
                        $remboursements = array_filter($historique, function($p) {
                            return isset($p['remboursement_status']);
                        });
                        ?>
                        
                        <?php if(empty($remboursements)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Vous n'avez aucun remboursement dans votre historique.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date de demande</th>
                                            <th>Place</th>
                                            <th>Montant</th>
                                            <th>Raison</th>
                                            <th>Statut</th>
                                            <th>Commentaire</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($remboursements as $paiement): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($paiement['date_remboursement'] ?? $paiement['date_paiement'])) ?></td>
                                                <td>N°<?= $paiement['place_numero'] ?></td>
                                                <td><strong><?= number_format($paiement['montant'], 2) ?> €</strong></td>
                                                <td><?= htmlspecialchars($paiement['raison'] ?? 'Non spécifiée') ?></td>
                                                <td>
                                                    <?php if($paiement['remboursement_status'] === 'effectué'): ?>
                                                        <span class="badge bg-success">Remboursé</span>
                                                    <?php elseif($paiement['remboursement_status'] === 'en_cours'): ?>
                                                        <span class="badge bg-warning text-dark">En cours</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Refusé</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if(!empty($paiement['commentaire_admin'])): ?>
                                                        <?= htmlspecialchars($paiement['commentaire_admin']) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
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
        </div>
    <?php endif; ?>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
