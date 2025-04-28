<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Modifier mon profil</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Votre profil a été mis à jour avec succès.
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
            <form action="index.php?controller=user&action=edit" method="POST">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="index.php?controller=user&action=profile" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>