<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <h1>Tableau de bord Administrateur</h1>
        
        <div class="stats-overview">
            <div class="stat-card">
                <h3>Places disponibles</h3>
                <p class="stat-number"><?= $stats['places_libres'] ?>/<?= $stats['places_totales'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Réservations aujourd'hui</h3>
                <p class="stat-number"><?= $stats['reservations_jour'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Revenus du mois</h3>
                <p class="stat-number"><?= number_format($stats['revenus_mois'], 2) ?> €</p>
            </div>
        </div>
        
        <div class="charts-container">
            <div class="chart-wrapper">
                <h3>Occupation par jour</h3>
                <canvas id="occupationChart"></canvas>
            </div>
            <div class="chart-wrapper">
                <h3>Revenus mensuels</h3>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>Actions rapides</h3>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/?page=admin&action=users" class="btn-primary">Gérer les utilisateurs</a>
                <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn-primary">Gérer les places</a>
                <a href="<?= BASE_URL ?>/?page=admin&action=refunds" class="btn-primary">Gérer les remboursements</a>
            </div>
        </div>
    </div>

    <script>
        // Données pour les graphiques
        const occupationData = <?= json_encode($stats['occupation_semaine']) ?>;
        const revenueData = <?= json_encode($stats['revenus_mois_detail']) ?>;
        
        // Graphique d'occupation
        const occupationChart = new Chart(
            document.getElementById('occupationChart'),
            {
                type: 'bar',
                data: {
                    labels: occupationData.map(row => row.jour),
                    datasets: [{
                        label: 'Places occupées',
                        data: occupationData.map(row => row.places_occupees)
                    }]
                }
            }
        );
        
        // Graphique des revenus
        const revenueChart = new Chart(
            document.getElementById('revenueChart'),
            {
                type: 'line',
                data: {
                    labels: revenueData.map(row => row.jour),
                    datasets: [{
                        label: 'Revenus (€)',
                        data: revenueData.map(row => row.montant)
                    }]
                }
            }
        );
    </script>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
