<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Ajouter un utilisateur</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Email*</label>
                <input type="email" name="email" required value="<?= $_POST['email'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label>Mot de passe*</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Nom*</label>
                <input type="text" name="nom" required value="<?= $_POST['nom'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" value="<?= $_POST['prenom'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label>Téléphone</label>
                <input type="tel" name="telephone" value="<?= $_POST['telephone'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label>Rôle</label>
                <select name="role">
                    <option value="user" <?= isset($_POST['role']) && $_POST['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                    <option value="admin" <?= isset($_POST['role']) && $_POST['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                </select>
            </div>
            
            <button type="submit" class="btn-primary">Créer l'utilisateur</button>
            <a href="<?= BASE_URL ?>/?page=admin&action=users" class="btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
