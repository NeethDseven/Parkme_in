<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Tableau de bord</h1>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Réservations actives</h3>
                <p class="stat-number"><?= $stats['reservations_actives'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Total dépensé</h3>
                <p class="stat-number"><?= number_format($stats['total_depense'], 2) ?> €</p>
            </div>
            <!-- Autres statistiques -->
        </div>

        <div class="dashboard-actions">
            <a href="<?= BASE_URL ?>/?page=parking&action=list" class="action-button">
                Réserver une place
            </a>
            <a href="<?= BASE_URL ?>/?page=user&action=history" class="action-button">
                Historique
            </a>
        </div>
    </div>
</body>
</html>
