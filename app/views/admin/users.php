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
                    <h2 class="mb-0">Administration des utilisateurs</h2>
                    <div class="nav-buttons">
                        <a href="index.php?controller=dashboard&action=index" class="btn btn-outline-light me-2">
                            <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                        </a>
                        <a href="index.php?controller=dashboard&action=logout" class="btn btn-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <h3 class="section-title">Liste des utilisateurs</h3>
                        <a href="index.php?controller=admin&action=addUser" class="btn btn-success">
                            <i class="bi bi-person-plus-fill me-2"></i>Ajouter un utilisateur
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-shield-lock-fill me-1"></i>
                                                Administrateur
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info">
                                                <i class="bi bi-person-fill me-1"></i>
                                                Utilisateur
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['date_creation']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="index.php?controller=admin&action=editUser&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id'] && $user['role'] != 'admin'): ?>
                                            <a href="index.php?controller=admin&action=deleteUser&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
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
