<?php $pageTitle = 'Mes réservations - Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">Mes réservations</h1>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Vous n'avez aucune réservation.
        </div>
        
        <div class="mt-4">
            <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>
                Réserver une place
            </a>
        </div>
    <?php else: ?>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs mb-3" id="reservationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-content" 
                        type="button" role="tab" aria-controls="active-content" aria-selected="true">
                    Réservations actives
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-content" 
                        type="button" role="tab" aria-controls="history-content" aria-selected="false">
                    Historique
                </button>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content">
            <!-- Onglet réservations actives -->
            <div class="tab-pane fade show active" id="active-content" role="tabpanel" aria-labelledby="active-tab">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Réservations actives</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $hasActive = false;
                        foreach($reservations as $reservation) {
                            if ($reservation['status'] === 'confirmée') {
                                $hasActive = true;
                                break;
                            }
                        }
                        ?>
                        
                        <?php if (!$hasActive): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Vous n'avez aucune réservation active.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Place</th>
                                            <th>Type</th>
                                            <th>Date début</th>
                                            <th>Date fin</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($reservations as $reservation): ?>
                                            <?php if ($reservation['status'] === 'confirmée'): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($reservation['place_numero']) ?></td>
                                                    <td><?= htmlspecialchars($reservation['place_type']) ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></td>
                                                    <td><span class="badge bg-success">
                                                        <?= htmlspecialchars($reservation['status']) ?>
                                                    </span></td>
                                                    <td>
                                                        <a href="<?= BASE_URL ?>/?page=user&action=cancelReservation&id=<?= $reservation['id'] ?>"
                                                        onclick="return confirm('Voulez-vous annuler cette réservation ?')"
                                                        class="btn btn-sm btn-danger">
                                                            <i class="fas fa-times me-1"></i> Annuler
                                                        </a>
                                                        <a href="<?= BASE_URL ?>/?page=user&action=downloadReceipt&id=<?= $reservation['id'] ?>"
                                                        class="btn btn-sm btn-primary">
                                                            <i class="fas fa-file-pdf me-1"></i> PDF
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Onglet historique des réservations -->
            <div class="tab-pane fade" id="history-content" role="tabpanel" aria-labelledby="history-tab">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Historique des réservations</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $hasHistory = false;
                        foreach($reservations as $reservation) {
                            if ($reservation['status'] === 'annulée') {
                                $hasHistory = true;
                                break;
                            }
                        }
                        ?>
                        
                        <?php if (!$hasHistory): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Votre historique est vide.
                            </div>
                        <?php else: ?>
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
                                            <?php if ($reservation['status'] === 'annulée'): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($reservation['place_numero']) ?></td>
                                                    <td><?= htmlspecialchars($reservation['place_type']) ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></td>
                                                    <td><span class="badge bg-secondary">
                                                        <?= htmlspecialchars($reservation['status']) ?>
                                                    </span></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>
                Réserver une place
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Ajouter le script Bootstrap pour activer les onglets au bas de la page -->
<script>
    // Assurons-nous que le DOM est chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Créer des instances d'onglets Bootstrap
        var triggerTabList = [].slice.call(document.querySelectorAll('#reservationTabs button'))
        triggerTabList.forEach(function(triggerEl) {
            new bootstrap.Tab(triggerEl)
        });
    });
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>
