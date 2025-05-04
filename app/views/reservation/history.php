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
                    <h2 class="mb-0">Historique des réservations</h2>
                    <div class="nav-buttons">
                        <a href="index.php?controller=reservation&action=index" class="btn btn-outline-light me-2">
                            <i class="bi bi-calendar-check me-1"></i> Mes réservations
                        </a>
                        <a href="index.php?controller=dashboard&action=index" class="btn btn-light">
                            <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="mb-4">
                        <h3 class="section-title">Réservations passées</h3>
                    </div>
                    
                    <?php if (!empty($reservations)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Place N°</th>
                                        <th scope="col">Date de début</th>
                                        <th scope="col">Date de fin</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col">Durée</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $res): ?>
                                    <tr <?= (isset($res['statut']) && $res['statut'] === 'annulée') ? 'class="table-danger bg-opacity-25"' : '' ?>>
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
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle-fill me-1"></i>Annulée
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Terminée
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $debut = new DateTime($res['date_debut']);
                                                $fin = new DateTime($res['date_fin']);
                                                $duree = $debut->diff($fin);
                                                
                                                if ($duree->days > 0) {
                                                    echo $duree->days . ' jour(s) et ' . $duree->h . ' heure(s)';
                                                } else {
                                                    echo $duree->h . ' heure(s) et ' . $duree->i . ' minute(s)';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination component -->
                        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
                            <div class="d-flex justify-content-center mt-4">
                                <nav aria-label="Pagination des réservations">
                                    <ul class="pagination">
                                        <?php if ($pagination['hasPrevPage']): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?controller=reservation&action=history&page=<?= $pagination['page'] - 1 ?>" aria-label="Précédent">
                                                    <span aria-hidden="true">&laquo;</span>
                                                    <span class="sr-only">Précédent</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&laquo;</span>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        // Afficher un nombre limité de liens de page
                                        $startPage = max(1, $pagination['page'] - 2);
                                        $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                                        
                                        // Assurer un minimum de 5 liens si possible
                                        if ($endPage - $startPage < 4 && $pagination['totalPages'] > 4) {
                                            if ($startPage == 1) {
                                                $endPage = min($pagination['totalPages'], 5);
                                            } else {
                                                $startPage = max(1, $pagination['totalPages'] - 4);
                                            }
                                        }
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                                <a class="page-link" href="index.php?controller=reservation&action=history&page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($pagination['hasNextPage']): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?controller=reservation&action=history&page=<?= $pagination['page'] + 1 ?>" aria-label="Suivant">
                                                    <span aria-hidden="true">&raquo;</span>
                                                    <span class="sr-only">Suivant</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&raquo;</span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                        <!-- End Pagination component -->
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i> Aucune réservation passée trouvée
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
