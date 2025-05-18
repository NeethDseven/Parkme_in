<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une place</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Ajouter une place</h1>
        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Numéro de place :</label>
                <input type="text" name="numero" required>
            </div>
            <div class="form-group">
                <label>Type :</label>
                <select name="type" required>
                    <option value="standard">Standard</option>
                    <option value="handicape">Handicapé</option>
                    <option value="electrique">Véhicule électrique</option>
                </select>
            </div>
            <button type="submit">Ajouter</button>
            <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
