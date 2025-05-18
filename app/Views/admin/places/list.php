<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des places</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Gestion des places de parking</h1>
        <a href="<?= BASE_URL ?>/?page=admin&action=addPlace" class="btn-primary">Ajouter une place</a>
        
        <table>
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($places as $place): ?>
                <tr>
                    <td><?= htmlspecialchars($place['numero']) ?></td>
                    <td><?= htmlspecialchars($place['type']) ?></td>
                    <td><?= htmlspecialchars($place['status']) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/?page=admin&action=editPlace&id=<?= $place['id'] ?>">Modifier</a>
                        <a href="<?= BASE_URL ?>/?page=admin&action=deletePlace&id=<?= $place['id'] ?>" 
                           onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
