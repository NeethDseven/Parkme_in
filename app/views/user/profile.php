<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Mon profil</h1>
    
    <?php if (isset($user) && $user): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Informations personnelles</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nom:</strong> <?= htmlspecialchars($user['nom'] ?? '') ?></p>
                        <p><strong>Prénom:</strong> <?= htmlspecialchars($user['prenom'] ?? '') ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                        <p><strong>Téléphone:</strong> <?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></p>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="index.php?controller=user&action=edit" class="btn btn-primary">Modifier mon profil</a>
                    <a href="index.php?controller=user&action=changePassword" class="btn btn-outline-secondary">Changer mon mot de passe</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            Impossible de récupérer vos informations. Veuillez réessayer plus tard.
        </div>
    <?php endif; ?>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
