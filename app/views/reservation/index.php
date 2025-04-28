<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

// Add this at the top of the file
require_once __DIR__ . '/../../models/ParkingSpot.php';

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container">
    <div class="row my-4">
        <div class="col-12">
            <div class="card shadow fade-in">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Mes réservations</h2>
                    <div>
                        <a href="index.php?controller=reservation&action=history" class="btn btn-outline-light me-2">
                            <i class="bi bi-clock-history me-1"></i> Historique
                        </a>
                        <a href="index.php?controller=dashboard&action=index" class="btn btn-light">
                            <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Notification de succès -->
                    <?php if (isset($_GET['success']) && $_GET['success'] == 'cancel'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> Votre réservation a été annulée avec succès.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Réservations actives -->
                    <div class="mb-4">
                        <h3 class="section-title">Réservations actives</h3>
                    </div>
                    
                    <?php if (empty($activeReservations)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i> Aucune réservation active trouvée
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Place N°</th>
                                        <th scope="col">Date de début</th>
                                        <th scope="col">Date de fin</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activeReservations as $res): ?>
                                    <tr>
                                        <td><?php 
                                            if (isset($res->emplacement_id)) {
                                                echo htmlspecialchars($res->emplacement_id); 
                                            } else {
                                                echo 'N/A';
                                            }
                                        ?></td>
                                        <td><?php echo htmlspecialchars($res->date_debut ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($res->date_fin ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($res->statut ?? ''); ?></td>
                                        <td>
                                            <a href="/projet/Parkme_in-master/index.php?controller=reservation&action=confirmation&id=<?php echo $res->id; ?>" class="btn btn-info btn-sm">Afficher</a>
                                            
                                            <?php 
                                            $status = strtolower($res->statut);
                                            if ($status == 'confirmée' || $status == 'en_cours'): 
                                            ?>
                                            <a href="/projet/Parkme_in-master/index.php?controller=reservation&action=cancel&id=<?php echo $res->id; ?>" class="btn btn-warning btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation?')">Annuler</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Section des boutons -->
                    <div class="row mt-4">
                        <div class="col-12 mb-3">
                            <div class="d-grid">
                                <a href="index.php?controller=reservation&action=create" class="btn btn-success">
                                    <i class="bi bi-plus-circle me-2"></i> Nouvelle réservation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
