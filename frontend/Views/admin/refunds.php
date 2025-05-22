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
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (empty($remboursements)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucune demande de remboursement en attente.
                </div>
            <?php else: ?>
                <ul class="nav nav-tabs mb-3" id="refundsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-selected="true">En attente</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="processed-tab" data-bs-toggle="tab" data-bs-target="#processed" type="button" role="tab" aria-selected="false">Traités</button>
                    </li>
                </ul>
                
                <div class="tab-content" id="refundsTabContent">
                    <!-- Demandes en attente -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Date demande</th>
                                        <th>Client</th>
                                        <th>Réservation</th>
                                        <th>Montant</th>
                                        <th>Raison</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($remboursements as $remb): ?>
                                        <?php if($remb['status'] === 'en_cours'): ?>
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
                                                    <button type="button" class="btn btn-sm btn-success mb-1" data-bs-toggle="modal" data-bs-target="#acceptModal<?= $remb['id'] ?>">
                                                        <i class="fas fa-check me-1"></i> Accepter
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#refuseModal<?= $remb['id'] ?>">
                                                        <i class="fas fa-times me-1"></i> Refuser
                                                    </button>
                                                    
                                                    <!-- Modal d'acceptation -->
                                                    <div class="modal fade" id="acceptModal<?= $remb['id'] ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form action="<?= BASE_URL ?>/?page=admin&action=processRefund" method="post">
                                                                    <input type="hidden" name="remboursement_id" value="<?= $remb['id'] ?>">
                                                                    <input type="hidden" name="decision" value="accepte">
                                                                    
                                                                    <div class="modal-header bg-success text-white">
                                                                        <h5 class="modal-title">Accepter le remboursement</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p>Vous êtes sur le point d'accepter le remboursement de <strong><?= number_format($remb['montant'], 2) ?> €</strong> pour l'utilisateur <strong><?= htmlspecialchars($remb['prenom'] . ' ' . $remb['nom']) ?></strong>.</p>
                                                                        
                                                                        <div class="alert alert-info">
                                                                            <div class="mb-2"><strong>Raison de la demande:</strong></div>
                                                                            <p class="mb-0"><?= htmlspecialchars($remb['raison']) ?></p>
                                                                        </div>
                                                                        
                                                                        <div class="form-group mb-3">
                                                                            <label for="commentaire_acceptation<?= $remb['id'] ?>" class="form-label">Commentaire (visible par l'utilisateur):</label>
                                                                            <textarea id="commentaire_acceptation<?= $remb['id'] ?>" name="commentaire_admin" class="form-control" rows="3" placeholder="Raison de l'acceptation du remboursement..."></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                        <button type="submit" class="btn btn-success">Confirmer</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Modal de refus -->
                                                    <div class="modal fade" id="refuseModal<?= $remb['id'] ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form action="<?= BASE_URL ?>/?page=admin&action=processRefund" method="post">
                                                                    <input type="hidden" name="remboursement_id" value="<?= $remb['id'] ?>">
                                                                    <input type="hidden" name="decision" value="refuse">
                                                                    
                                                                    <div class="modal-header bg-danger text-white">
                                                                        <h5 class="modal-title">Refuser le remboursement</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p>Vous êtes sur le point de refuser le remboursement de <strong><?= number_format($remb['montant'], 2) ?> €</strong> pour l'utilisateur <strong><?= htmlspecialchars($remb['prenom'] . ' ' . $remb['nom']) ?></strong>.</p>
                                                                        
                                                                        <div class="alert alert-warning">
                                                                            <div class="mb-2"><strong>Raison de la demande:</strong></div>
                                                                            <p class="mb-0"><?= htmlspecialchars($remb['raison']) ?></p>
                                                                        </div>
                                                                        
                                                                        <div class="form-group mb-3">
                                                                            <label for="commentaire_refus<?= $remb['id'] ?>" class="form-label">Motif du refus (obligatoire) :</label>
                                                                            <textarea id="commentaire_refus<?= $remb['id'] ?>" name="commentaire_admin" class="form-control" rows="3" placeholder="Raison du refus du remboursement..." required></textarea>
                                                                            <div class="form-text">Ce commentaire sera visible par l'utilisateur.</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                        <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Demandes traitées -->
                    <div class="tab-pane fade" id="processed" role="tabpanel" aria-labelledby="processed-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Date demande</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Raison</th>
                                        <th>Statut</th>
                                        <th>Commentaire admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($remboursements as $remb): ?>
                                        <?php if($remb['status'] !== 'en_cours'): ?>
                                            <tr class="<?= $remb['status'] === 'effectué' ? 'table-success' : 'table-danger' ?>">
                                                <td><?= date('d/m/Y H:i', strtotime($remb['date_demande'])) ?></td>
                                                <td><?= htmlspecialchars($remb['nom']) ?> <?= htmlspecialchars($remb['prenom']) ?></td>
                                                <td><strong class="text-primary"><?= number_format($remb['montant'], 2) ?> €</strong></td>
                                                <td><?= htmlspecialchars($remb['raison']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $remb['status'] === 'effectué' ? 'success' : 'danger' ?>">
                                                        <?= $remb['status'] === 'effectué' ? 'Accepté' : 'Refusé' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if(!empty($remb['commentaire_admin'])): ?>
                                                        <?= htmlspecialchars($remb['commentaire_admin']) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
