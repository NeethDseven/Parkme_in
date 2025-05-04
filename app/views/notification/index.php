<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container">
    <div class="row my-4">
        <div class="col-12">
            <div class="card shadow fade-in">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Mes notifications</h2>
                    <div class="nav-buttons">
                        <a href="index.php?controller=notification&action=deleteAll" class="btn btn-outline-light me-2"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer toutes vos notifications ?');">
                            <i class="bi bi-trash me-1"></i> Tout supprimer
                        </a>
                        <a href="index.php?controller=dashboard&action=index" class="btn btn-light">
                            <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i> Vous n'avez aucune notification.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="list-group-item list-group-item-action <?= $notification['lu'] ? '' : 'list-group-item-primary' ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">
                                            <?php
                                            $icon = '';
                                            switch ($notification['type']) {
                                                case 'reservation':
                                                    $icon = '<i class="bi bi-calendar-check text-success me-2"></i>';
                                                    break;
                                                case 'rappel':
                                                    $icon = '<i class="bi bi-clock-history text-warning me-2"></i>';
                                                    break;
                                                case 'paiement':
                                                    $icon = '<i class="bi bi-credit-card text-info me-2"></i>';
                                                    break;
                                                case 'annulation':
                                                    $icon = '<i class="bi bi-x-circle text-danger me-2"></i>';
                                                    break;
                                                default:
                                                    $icon = '<i class="bi bi-bell me-2"></i>';
                                            }
                                            echo $icon;
                                            
                                            // Afficher le début du message comme titre
                                            $firstLine = explode('.', $notification['message'])[0];
                                            echo htmlspecialchars($firstLine);
                                            ?>
                                        </h5>
                                        <small><?= date('d/m/Y H:i', strtotime($notification['date_creation'])) ?></small>
                                    </div>
                                    <p class="mb-1"><?= nl2br(htmlspecialchars($notification['message'])) ?></p>
                                    <div class="d-flex justify-content-end">
                                        <?php if (!$notification['lu']): ?>
                                            <a href="index.php?controller=notification&action=markAsRead&id=<?= $notification['id'] ?>" class="btn btn-sm btn-primary me-2">
                                                <i class="bi bi-check-circle me-1"></i> Marquer comme lu
                                            </a>
                                        <?php endif; ?>
                                        <a href="index.php?controller=notification&action=delete&id=<?= $notification['id'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?');">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
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

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
