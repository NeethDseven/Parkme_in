<?php $pageTitle = 'Gestion des places - Administration Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Gestion des places de parking</h1>
            <a href="<?= BASE_URL ?>/?page=admin&action=addPlace" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Ajouter une place
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($places as $place): ?>
                        <tr>
                            <td><?= htmlspecialchars($place['numero']) ?></td>
                            <td>
                                <?php
                                switch($place['type']) {
                                    case 'standard':
                                        echo '<span class="badge bg-primary">Standard</span>';
                                        break;
                                    case 'handicape':
                                        echo '<span class="badge bg-info">Handicapé</span>';
                                        break;
                                    case 'electrique':
                                        echo '<span class="badge bg-success">Électrique</span>';
                                        break;
                                    default:
                                        echo htmlspecialchars($place['type']);
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                switch($place['status']) {
                                    case 'libre':
                                        echo '<span class="badge bg-success">Libre</span>';
                                        break;
                                    case 'occupe':
                                        echo '<span class="badge bg-danger">Occupé</span>';
                                        break;
                                    case 'maintenance':
                                        echo '<span class="badge bg-warning">Maintenance</span>';
                                        break;
                                    default:
                                        echo htmlspecialchars($place['status']);
                                }
                                ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/?page=admin&action=editPlace&id=<?= $place['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="<?= BASE_URL ?>/?page=admin&action=deletePlace&id=<?= $place['id'] ?>" 
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette place ?')">
                                    <i class="fas fa-trash"></i> Supprimer
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

<?php require_once 'app/Views/layouts/footer.php'; ?>
