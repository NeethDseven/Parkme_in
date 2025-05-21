<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des tarifs</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Gestion des tarifs</h1>
        
        <form method="POST" class="tarifs-form">
            <?php foreach($tarifs as $tarif): ?>
            <div class="tarif-section">
                <h3><?= ucfirst($tarif['type_place']) ?></h3>
                <div class="form-group">
                    <label>Prix par heure</label>
                    <input type="number" step="0.01" name="tarifs[<?= $tarif['id'] ?>][prix_heure]" 
                           value="<?= $tarif['prix_heure'] ?>">
                </div>
                <div class="form-group">
                    <label>Prix par jour</label>
                    <input type="number" step="0.01" name="tarifs[<?= $tarif['id'] ?>][prix_journee]" 
                           value="<?= $tarif['prix_journee'] ?>">
                </div>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn-primary">Mettre à jour les tarifs</button>
        </form>
    </div>
</body>
</html>
