<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';

// Récupérer les informations de l'utilisateur connecté
$userName = $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom'];
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3">Bonjour, <?= htmlspecialchars($userName) ?></h1>
            <p class="text-muted">Bienvenue sur votre tableau de bord ParkMeIn</p>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100 fade-in">
                <div class="card-body">
                    <h5 class="card-title text-muted">Réservations totales</h5>
                    <div class="d-flex align-items-center mt-3">
                        <div class="bg-primary bg-opacity-25 rounded-circle p-3 me-3">
                            <i class="bi bi-calendar-check text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                        <h2 class="mb-0"><?= $totalReservations ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 fade-in">
                <div class="card-body">
                    <h5 class="card-title text-muted">Réservations actives</h5>
                    <div class="d-flex align-items-center mt-3">
                        <div class="bg-success bg-opacity-25 rounded-circle p-3 me-3">
                            <i class="bi bi-car-front-fill text-success" style="font-size: 1.5rem;"></i>
                        </div>
                        <h2 class="mb-0"><?= count($activeReservations) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 fade-in">
                <div class="card-body">
                    <h5 class="card-title text-muted">Réservations à venir</h5>
                    <div class="d-flex align-items-center mt-3">
                        <div class="bg-warning bg-opacity-25 rounded-circle p-3 me-3">
                            <i class="bi bi-clock text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                        <h2 class="mb-0"><?= count($upcomingReservations) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 fade-in">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total dépensé</h5>
                    <div class="d-flex align-items-center mt-3">
                        <div class="bg-info bg-opacity-25 rounded-circle p-3 me-3">
                            <i class="bi bi-currency-euro text-info" style="font-size: 1.5rem;"></i>
                        </div>
                        <h2 class="mb-0"><?= number_format($totalSpent, 2, ',', ' ') ?> €</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Réservations actives -->
        <div class="col-md-6">
            <div class="card shadow-sm fade-in">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Réservations actives</h2>
                    <a href="index.php?controller=reservation&action=index" class="btn btn-sm btn-outline-primary">Voir toutes</a>
                </div>
                <div class="card-body">
                    <?php if (empty($activeReservations)): ?>
                        <div class="text-center text-muted my-4">
                            <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                            <p class="mt-2">Vous n'avez aucune réservation active pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activeReservations as $reservation): ?>
                            <div class="card mb-3 border-success border-start border-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="bi bi-p-square-fill me-1 text-secondary"></i>
                                            <?= htmlspecialchars($reservation['parking_nom']) ?> - Place <?= htmlspecialchars($reservation['numero_place']) ?>
                                        </h6>
                                        <span class="badge bg-success">En cours</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Début:</small>
                                            <div><?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Fin:</small>
                                            <div><?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">Code d'accès:</small>
                                            <span class="ms-2 badge bg-primary"><?= $reservation['code_acces'] ?></span>
                                        </div>
                                        <a href="index.php?controller=reservation&action=view&id=<?= $reservation['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i> Détails
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Réservations à venir -->
        <div class="col-md-6">
            <div class="card shadow-sm fade-in">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Réservations à venir</h2>
                    <a href="index.php?controller=reservation&action=index" class="btn btn-sm btn-outline-primary">Voir toutes</a>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingReservations)): ?>
                        <div class="text-center text-muted my-4">
                            <i class="bi bi-calendar" style="font-size: 2rem;"></i>
                            <p class="mt-2">Vous n'avez aucune réservation à venir.</p>
                            <a href="index.php?controller=reservation&action=create" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle me-1"></i> Nouvelle réservation
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcomingReservations as $reservation): ?>
                            <div class="card mb-3 border-warning border-start border-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="bi bi-p-square-fill me-1 text-secondary"></i>
                                            <?= htmlspecialchars($reservation['parking_nom']) ?> - Place <?= htmlspecialchars($reservation['numero_place']) ?>
                                        </h6>
                                        <span class="badge bg-warning text-dark">À venir</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Début:</small>
                                            <div><?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Fin:</small>
                                            <div><?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">Code d'accès:</small>
                                            <span class="ms-2 badge bg-primary"><?= $reservation['code_acces'] ?></span>
                                        </div>
                                        <div>
                                            <a href="index.php?controller=reservation&action=view&id=<?= $reservation['id'] ?>" class="btn btn-sm btn-outline-secondary me-1">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="index.php?controller=reservation&action=cancel&id=<?= $reservation['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($upcomingReservations)): ?>
                <div class="card-footer text-center">
                    <a href="index.php?controller=reservation&action=create" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Nouvelle réservation
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notifications récentes et liens rapides -->
    <div class="row mb-4">
        <!-- Notifications récentes -->
        <div class="col-md-8">
            <div class="card shadow-sm fade-in">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Notifications récentes</h2>
                    <a href="index.php?controller=notification&action=index" class="btn btn-sm btn-outline-primary">Voir toutes</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentNotifications)): ?>
                        <div class="text-center text-muted my-4">
                            <i class="bi bi-bell-slash" style="font-size: 2rem;"></i>
                            <p class="mt-2">Vous n'avez aucune notification non lue.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recentNotifications as $notification): ?>
                                <a href="index.php?controller=notification&action=markAsRead&id=<?= $notification['id'] ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
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
                                        
                                        // Extraire la première phrase du message
                                        $firstLine = explode('.', $notification['message'])[0];
                                        ?>
                                        <h6 class="mb-1"><?= $icon ?><?= htmlspecialchars($firstLine) ?></h6>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($notification['date_creation'])) ?></small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Liens rapides -->
        <div class="col-md-4">
            <div class="card shadow-sm fade-in">
                <div class="card-header bg-light">
                    <h2 class="h5 mb-0">Liens rapides</h2>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="index.php?controller=reservation&action=create" class="btn btn-primary">
                            <i class="bi bi-calendar-plus me-2"></i> Nouvelle réservation
                        </a>
                        <a href="index.php?controller=reservation&action=index" class="btn btn-outline-secondary">
                            <i class="bi bi-calendar-check me-2"></i> Mes réservations
                        </a>
                        <a href="index.php?controller=payment&action=history" class="btn btn-outline-secondary">
                            <i class="bi bi-credit-card me-2"></i> Historique des paiements
                        </a>
                        <a href="index.php?controller=user&action=profile" class="btn btn-outline-secondary">
                            <i class="bi bi-person me-2"></i> Mon profil
                        </a>
                        <a href="index.php?controller=home&action=contact" class="btn btn-outline-secondary">
                            <i class="bi bi-envelope me-2"></i> Nous contacter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
