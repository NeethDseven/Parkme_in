<?php $pageTitle = 'Gestion des utilisateurs - Administration Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Gestion des utilisateurs</h1>
            <a href="<?= BASE_URL ?>/?page=admin&action=addUser" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i> Ajouter un utilisateur
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
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['nom']) ?></td>
                            <td><?= htmlspecialchars($user['prenom']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php if($user['role'] === 'admin'): ?>
                                    <span class="badge bg-danger">Administrateur</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Utilisateur</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/?page=admin&action=editUser&id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="<?= BASE_URL ?>/?page=admin&action=deleteUser&id=<?= $user['id'] ?>" 
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
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
