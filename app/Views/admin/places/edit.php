<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une place</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Modifier la place</h1>
        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Numéro de place :</label>
                <input type="text" name="numero" value="<?= htmlspecialchars($place['numero']) ?>" required>
            </div>
            <div class="form-group">
                <label>Type :</label>
                <select name="type" required>
                    <option value="standard" <?= $place['type'] === 'standard' ? 'selected' : '' ?>>Standard</option>
                    <option value="handicape" <?= $place['type'] === 'handicape' ? 'selected' : '' ?>>Handicapé</option>
                    <option value="electrique" <?= $place['type'] === 'electrique' ? 'selected' : '' ?>>Véhicule électrique</option>
                </select>
            </div>
            <div class="form-group">
                <label>Statut :</label>
                <select name="status" required>
                    <option value="libre" <?= $place['status'] === 'libre' ? 'selected' : '' ?>>Libre</option>
                    <option value="occupe" <?= $place['status'] === 'occupe' ? 'selected' : '' ?>>Occupé</option>
                    <option value="maintenance" <?= $place['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </div>
            <button type="submit">Enregistrer</button>
            <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
