<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Changer mon mot de passe</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Votre mot de passe a été mis à jour avec succès.
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form action="index.php?controller=user&action=changePassword" method="POST">
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <small class="form-text text-muted">Le mot de passe doit contenir au moins 6 caractères.</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
                <a href="index.php?controller=user&action=profile" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
