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
                    <h2 class="mb-0">Tableau de bord Administrateur</h2>
                    <div class="nav-buttons">
                        <a href="index.php?controller=dashboard&action=logout" class="btn btn-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Carte pour les statistiques générales -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i> Statistiques</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <div class="border p-3 rounded text-center">
                                                <h4><?= $stats['userCount'] ?? 0 ?></h4>
                                                <span class="text-muted">Utilisateurs</span>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="border p-3 rounded text-center">
                                                <h4><?= $stats['reservationCount'] ?? 0 ?></h4>
                                                <span class="text-muted">Réservations</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border p-3 rounded text-center">
                                                <h4><?= $stats['activeReservationCount'] ?? 0 ?></h4>
                                                <span class="text-muted">Réservations actives</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border p-3 rounded text-center">
                                                <h4><?= $stats['parkingSpotCount'] ?? 0 ?></h4>
                                                <span class="text-muted">Places de parking</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Carte pour les revenus -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i> Revenus</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <div class="border p-3 rounded text-center">
                                                <h4><?= number_format($stats['totalRevenue'] ?? 0, 2, ',', ' ') ?> €</h4>
                                                <span class="text-muted">Revenus totaux</span>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="border p-3 rounded text-center">
                                                <h4><?= number_format($stats['monthlyRevenue'] ?? 0, 2, ',', ' ') ?> €</h4>
                                                <span class="text-muted">Revenus mensuels</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border p-3 rounded text-center">
                                                <h4><?= number_format($stats['weeklyRevenue'] ?? 0, 2, ',', ' ') ?> €</h4>
                                                <span class="text-muted">Revenus hebdo.</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border p-3 rounded text-center">
                                                <h4><?= number_format($stats['dailyRevenue'] ?? 0, 2, ',', ' ') ?> €</h4>
                                                <span class="text-muted">Revenus quotidiens</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section des liens rapides -->
                        <div class="col-12">
                            <h3 class="mb-3">Gestion administrative</h3>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="index.php?controller=admin&action=users" class="btn btn-primary d-block py-3">
                                        <i class="bi bi-people-fill me-2"></i> Gestion des utilisateurs
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="index.php?controller=admin&action=reservations" class="btn btn-warning d-block py-3">
                                        <i class="bi bi-calendar-check me-2"></i> Gestion des réservations
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="index.php?controller=admin&action=parkingSpots" class="btn btn-info d-block py-3">
                                        <i class="bi bi-p-square-fill me-2"></i> Gestion des places
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="index.php?controller=admin&action=reports" class="btn btn-secondary d-block py-3">
                                        <i class="bi bi-file-earmark-bar-graph me-2"></i> Rapports
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Activité récente - réservations -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Réservations récentes</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recentReservations)): ?>
                                        <div class="alert alert-info">Aucune réservation récente</div>
                                    <?php else: ?>
                                        <div class="list-group">
                                            <?php foreach ($recentReservations as $res): ?>
                                                <a href="index.php?controller=admin&action=viewReservation&id=<?= $res['id'] ?>" 
                                                   class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1">
                                                            <?php if ($res['statut'] === 'annulée'): ?>
                                                                <span class="badge bg-danger me-1">Annulée</span>
                                                            <?php elseif ($res['statut'] === 'confirmée'): ?>
                                                                <span class="badge bg-success me-1">Confirmée</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning me-1"><?= ucfirst($res['statut']) ?></span>
                                                            <?php endif; ?>
                                                            #<?= $res['id'] ?> - <?= htmlspecialchars($res['prenom'] . ' ' . $res['nom']) ?>
                                                        </h6>
                                                        <small><?= date('d/m/Y H:i', strtotime($res['date_reservation'])) ?></small>
                                                    </div>
                                                    <small><?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?> → <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?></small>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer text-end">
                                    <a href="index.php?controller=admin&action=reservations" class="btn btn-sm btn-outline-secondary">
                                        Voir toutes les réservations <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nouveaux utilisateurs -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i> Nouveaux utilisateurs</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($newUsers)): ?>
                                        <div class="alert alert-info">Aucun nouvel utilisateur</div>
                                    <?php else: ?>
                                        <div class="list-group">
                                            <?php foreach ($newUsers as $user): ?>
                                                <a href="index.php?controller=admin&action=editUser&id=<?= $user['id'] ?>" 
                                                   class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1">
                                                            <?php if ($user['role'] === 'admin'): ?>
                                                                <span class="badge bg-danger me-1">Admin</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-info me-1">Utilisateur</span>
                                                            <?php endif; ?>
                                                            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                                                        </h6>
                                                        <small><?= date('d/m/Y', strtotime($user['date_creation'])) ?></small>
                                                    </div>
                                                    <small><?= htmlspecialchars($user['email']) ?></small>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer text-end">
                                    <a href="index.php?controller=admin&action=users" class="btn btn-sm btn-outline-secondary">
                                        Voir tous les utilisateurs <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
