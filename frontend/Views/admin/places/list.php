<?php $pageTitle = 'Gestion des places - Administration Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Gestion des places de parking</h1>
            <a href="<?= BASE_URL ?>/?page=admin&action=addPlace" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Ajouter une place
            </a>
        </div>
        <div class="card-body">
            <!-- Messages de feedback -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['info'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['info']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['info']); ?>
            <?php endif; ?>
            
            <!-- Filtres -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form action="<?= BASE_URL ?>/" method="GET" class="d-flex gap-2">
                        <input type="hidden" name="page" value="admin">
                        <input type="hidden" name="action" value="places">
                        
                        <select name="type" class="form-select">
                            <option value="">Tous les types</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= $type ?>" <?= $typeFilter === $type ? 'selected' : '' ?>>
                                    <?php 
                                    switch($type) {
                                        case 'standard': echo 'Standard'; break;
                                        case 'handicape': echo 'PMR'; break;
                                        case 'electrique': echo 'Électrique'; break;
                                        default: echo ucfirst($type);
                                    }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                                    <?php 
                                    switch($status) {
                                        case 'libre': echo 'Libre'; break;
                                        case 'occupe': echo 'Occupée'; break;
                                        case 'maintenance': echo 'En maintenance'; break;
                                        default: echo ucfirst($status);
                                    }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-filter me-1"></i> Filtrer
                        </button>
                        
                        <?php if ($typeFilter || $statusFilter): ?>
                            <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Réinitialiser
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted">
                        <?= $totalPlaces ?> place(s) au total
                    </span>
                </div>
            </div>
            
            <!-- Liste des places -->
            <?php if (empty($places)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucune place trouvée.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Réservations actives</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($places as $place): ?>
                                <tr>
                                    <td><?= htmlspecialchars($place['numero']) ?></td>
                                    <td>
                                        <?php 
                                        switch($place['type']) {
                                            case 'standard': echo 'Standard'; break;
                                            case 'handicape': echo 'PMR'; break;
                                            case 'electrique': echo 'Électrique'; break;
                                            default: echo ucfirst(htmlspecialchars($place['type']));
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            switch ($place['status']) {
                                                case 'libre': echo 'bg-success'; break;
                                                case 'occupe': echo 'bg-danger'; break;
                                                case 'maintenance': echo 'bg-warning'; break;
                                                default: echo 'bg-secondary';
                                            }
                                        ?>">
                                            <?php 
                                            switch($place['status']) {
                                                case 'libre': echo 'Libre'; break;
                                                case 'occupe': echo 'Occupée'; break;
                                                case 'maintenance': echo 'En maintenance'; break;
                                                default: echo ucfirst(htmlspecialchars($place['status']));
                                            }
                                            ?>
                                        </span>
                                        <?php if (isset($place['status_fixed'])): ?>
                                            <span class="badge bg-info ms-1" title="Statut corrigé automatiquement">
                                                <i class="fas fa-sync-alt"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $place['has_active_reservations'] ?> réservation(s)
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= BASE_URL ?>/?page=admin&action=editPlace&id=<?= $place['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/?page=admin&action=deletePlace&id=<?= $place['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette place ?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    <?= $paginationLinks ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
