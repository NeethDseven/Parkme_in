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
                    <h2 class="mb-0">Gestion des Places de Parking</h2>
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
                    <?php if (!empty($error)) echo '<div class="alert alert-danger">' . $error . '</div>'; ?>
                    <?php if (!empty($success)) echo '<div class="alert alert-success">' . $success . '</div>'; ?>
                    
                    <div class="mb-4">
                        <h3>Ajouter une nouvelle place</h3>
                        <form method="post" action="?controller=admin&action=addParkingSpot" class="row g-3">
                            <div class="col-md-4">
                                <label for="numero" class="form-label">Numéro de place</label>
                                <input type="text" class="form-control" id="numero" name="numero" required>
                            </div>
                            <div class="col-md-4">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="normale">Normale</option>
                                    <option value="handicapée">Handicapée</option>
                                    <option value="réservée">Réservée</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <option value="libre">Libre</option>
                                    <option value="occupée">Occupée</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-plus-circle me-2"></i>Ajouter
                                </button>
                            </div>
                        </form>
                    </div>

                    <h3 class="mt-4">Liste des places</h3>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Numéro</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($spots as $spot): ?>
                                <tr>
                                    <td><?= $spot['id'] ?></td>
                                    <td><?= htmlspecialchars($spot['numero']) ?></td>
                                    <td><?= $spot['type'] ?></td>
                                    <td>
                                        <?php if ($spot['statut'] == 'libre'): ?>
                                            <span class="badge bg-success">Libre</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Occupée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?controller=admin&action=deleteParkingSpot&id=<?= $spot['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette place ?')">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
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
