<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un utilisateur</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Modifier l'utilisateur</h1>
        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Nom :</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
            </div>
            <div class="form-group">
                <label>Prénom :</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Rôle :</label>
                <select name="role">
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                </select>
            </div>
            <button type="submit">Enregistrer</button>
            <a href="<?= BASE_URL ?>/?page=admin&action=users" class="btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
