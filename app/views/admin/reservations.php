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
                    <h2 class="mb-0">Gestion des réservations</h2>
                    <div class="nav-buttons">
                        <a href="index.php?controller=admin&action=users" class="btn btn-outline-light me-2">
                            <i class="bi bi-people-fill me-1"></i> Utilisateurs
                        </a>
                        <a href="index.php?controller=dashboard&action=index" class="btn btn-light">
                            <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <h3 class="section-title">Liste des réservations</h3>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Utilisateur</th>
                                    <th scope="col">Place</th>
                                    <th scope="col">Début</th>
                                    <th scope="col">Fin</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $res): ?>
                                <tr>
                                    <td><?= $res['id'] ?></td>
                                    <td><?= htmlspecialchars($res['prenom'] . ' ' . $res['nom']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-p-square-fill me-1"></i>
                                            <?= htmlspecialchars($res['numero_place']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar-event text-primary me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($res['date_debut'])) ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar-event-fill text-danger me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($res['date_fin'])) ?>
                                    </td>
                                    <td>
                                        <?php if (isset($res['statut']) && $res['statut'] === 'annulée'): ?>
                                            <span class="badge bg-danger">Annulée</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Confirmée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="?controller=admin&action=deleteReservation&id=<?= $res['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?');" 
                                               title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
