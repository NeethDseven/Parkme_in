<?php $pageTitle = 'Tableau de bord - Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="h2 mb-4">Tableau de bord</h1>
    
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Réservations actives</h5>
                    <div class="number"><?php echo $stats['reservations_actives']; ?></div>
                    <p class="card-text text-muted">
                        <i class="fas fa-calendar-check me-1"></i>
                        Réservations en cours ou à venir
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total dépensé</h5>
                    <div class="number"><?php echo number_format($stats['total_depense'], 2); ?>€</div>
                    <p class="card-text text-muted">
                        <i class="fas fa-wallet me-1"></i>
                        Montant total dépensé
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-calendar me-2 text-primary"></i> 
                        Prochaine réservation
                    </h5>
                    
                    <?php if ($stats['prochaine_reservation']): ?>
                        <div class="mt-3">
                            <p class="mb-1"><strong>Place n°<?php echo $stats['prochaine_reservation']['place_numero']; ?></strong></p>
                            <p class="mb-1">
                                <i class="far fa-calendar me-1"></i>
                                <?php echo date('d/m/Y', strtotime($stats['prochaine_reservation']['date_debut'])); ?>
                            </p>
                            <p class="mb-1">
                                <i class="far fa-clock me-1"></i>
                                <?php echo date('H:i', strtotime($stats['prochaine_reservation']['date_debut'])) . ' - ' . date('H:i', strtotime($stats['prochaine_reservation']['date_fin'])); ?>
                            </p>
                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>/?page=user&action=reservations" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i> Voir détails
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="mb-3 text-muted">Aucune réservation à venir</p>
                            <a href="<?php echo BASE_URL; ?>/?page=parking&action=list" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Réserver une place
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dernières réservations</h5>
                    <a href="<?php echo BASE_URL; ?>/?page=user&action=reservations" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php
                        // Récupération des dernières réservations
                        $stmt = $GLOBALS['db']->prepare("
                            SELECT r.*, ps.numero as place_numero 
                            FROM reservations r 
                            JOIN parking_spaces ps ON r.place_id = ps.id
                            WHERE r.user_id = ? 
                            ORDER BY r.created_at DESC 
                            LIMIT 5
                        ");
                        $stmt->execute([$_SESSION['user_id']]);
                        $lastReservations = $stmt->fetchAll();
                    ?>
                    
                    <?php if (empty($lastReservations)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Aucune réservation récente</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($lastReservations as $reservation): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Place n°<?php echo $reservation['place_numero']; ?></h6>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?>
                                                (<?php echo date('H:i', strtotime($reservation['date_debut'])); ?> - 
                                                <?php echo date('H:i', strtotime($reservation['date_fin'])); ?>)
                                            </small>
                                        </div>
                                        <span class="badge <?php 
                                            switch ($reservation['status']) {
                                                case 'confirmée': echo 'bg-success'; break;
                                                case 'annulée': echo 'bg-danger'; break;
                                                default: echo 'bg-secondary';
                                            }
                                        ?>">
                                            <?php echo $reservation['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Derniers paiements</h5>
                    <a href="<?php echo BASE_URL; ?>/?page=user&action=history" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php
                        // Récupération des derniers paiements
                        $stmt = $GLOBALS['db']->prepare("
                            SELECT p.*, r.date_debut, r.date_fin
                            FROM paiements p
                            JOIN reservations r ON p.reservation_id = r.id
                            WHERE r.user_id = ?
                            ORDER BY p.date_paiement DESC
                            LIMIT 5
                        ");
                        $stmt->execute([$_SESSION['user_id']]);
                        $lastPayments = $stmt->fetchAll();
                    ?>
                    
                    <?php if (empty($lastPayments)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Aucun paiement récent</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($lastPayments as $payment): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo number_format($payment['montant'], 2); ?>€</h6>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($payment['date_paiement'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge <?php 
                                            switch ($payment['status']) {
                                                case 'valide': echo 'bg-success'; break;
                                                case 'refuse': echo 'bg-danger'; break;
                                                case 'annule': echo 'bg-warning'; break;
                                                default: echo 'bg-secondary';
                                            }
                                        ?>">
                                            <?php echo $payment['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
