<?php $pageTitle = 'Mes notifications - Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2 mb-0">Mes notifications</h1>
        </div>
        <div class="col-md-4 text-end">
            <?php if(!empty($notifications)): ?>
                <a href="<?php echo BASE_URL; ?>/?page=user&action=mark_all_read" class="btn btn-outline-primary">
                    <i class="fas fa-check-double me-2"></i>
                    Tout marquer comme lu
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if(empty($notifications)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-bell-slash fa-4x text-muted"></i>
                </div>
                <h5>Vous n'avez pas de notification</h5>
                <p class="text-muted">Les notifications concernant vos réservations et paiements apparaîtront ici.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach($notifications as $notification): ?>
                <?php 
                    $class = '';
                    switch($notification['type']) {
                        case 'paiement':
                            $icon = 'fas fa-money-bill-wave';
                            $class = 'border-success';
                            break;
                        case 'reservation':
                            $icon = 'fas fa-calendar-check';
                            $class = 'border-primary';
                            break;
                        case 'rappel':
                            $icon = 'fas fa-clock';
                            $class = 'border-warning';
                            break;
                        case 'system':
                        default:
                            $icon = 'fas fa-info-circle';
                            $class = 'border-secondary';
                    }
                    
                    // Check if message is JSON
                    $message = $notification['message'];
                    $messageData = json_decode($notification['message'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // C'est un JSON valide, donc on peut l'utiliser pour un affichage plus riche
                        if (isset($messageData['montant'])) {
                            $message = "Votre paiement de {$messageData['montant']}€ pour la réservation #{$messageData['reservation_id']} a été {$messageData['status']}. ";
                        }
                    }
                ?>
                
                <div class="notification-item card border-start <?php echo $class; ?> shadow-sm mb-3 <?php echo $notification['lu'] ? 'read' : 'unread'; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">
                                <i class="<?php echo $icon; ?> me-2"></i>
                                <?php echo htmlspecialchars($notification['titre']); ?>
                            </h5>
                            <small class="text-muted date">
                                <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                            </small>
                        </div>
                        
                        <p class="mb-3"><?php echo htmlspecialchars($message); ?></p>
                        
                        <?php if(!$notification['lu']): ?>
                            <div class="text-end">
                                <a href="<?php echo BASE_URL; ?>/?page=user&action=mark_read&id=<?php echo $notification['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-check me-1"></i>
                                    Marquer comme lu
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
