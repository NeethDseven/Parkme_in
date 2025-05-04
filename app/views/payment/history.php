<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow fade-in">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Historique des paiements</h1>
                    <div class="nav-buttons">
                        <a href="index.php?controller=dashboard&action=index" class="btn btn-light">
                            <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Aucun paiement trouvé dans votre historique.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>N° Transaction</th>
                                        <th>Date</th>
                                        <th>Réservation</th>
                                        <th>Méthode</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($payment['transaction_id'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($payment['transaction_id']) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($payment['date_paiement'])) ?></td>
                                        <td>
                                            <a href="index.php?controller=reservation&action=view&id=<?= $payment['reservation_id'] ?>">
                                                #<?= $payment['reservation_id'] ?>
                                            </a>
                                            <div>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($payment['date_debut'])) ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($payment['methode'] === 'carte'): ?>
                                                <i class="bi bi-credit-card me-1"></i> Carte bancaire
                                            <?php elseif ($payment['methode'] === 'paypal'): ?>
                                                <i class="bi bi-paypal me-1"></i> PayPal
                                            <?php else: ?>
                                                <?= ucfirst(htmlspecialchars($payment['methode'])) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= number_format($payment['montant'], 2, ',', ' ') ?> €</strong></td>
                                        <td>
                                            <?php if ($payment['statut'] === 'complete'): ?>
                                                <span class="badge bg-success">Confirmé</span>
                                            <?php elseif ($payment['statut'] === 'en_attente'): ?>
                                                <span class="badge bg-warning">En attente</span>
                                            <?php elseif ($payment['statut'] === 'annule'): ?>
                                                <span class="badge bg-danger">Annulé</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($payment['statut'])) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="index.php?controller=reservation&action=view&id=<?= $payment['reservation_id'] ?>" 
                                                   class="btn btn-sm btn-outline-secondary" title="Voir la réservation">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if ($payment['statut'] === 'complete'): ?>
                                                <a href="index.php?controller=reservation&action=print&id=<?= $payment['reservation_id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Imprimer le reçu" target="_blank">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
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
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
