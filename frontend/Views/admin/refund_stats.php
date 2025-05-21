<?php require_once 'frontend/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques des remboursements</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <h1>Statistiques des remboursements</h1>
        
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total des demandes</h3>
                <p><?= $stats['total'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Montant total remboursé</h3>
                <p><?= number_format($stats['montant_total'], 2) ?> €</p>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="remboursementsChart"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('remboursementsChart');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($stats['par_mois'], 'mois')) ?>,
                    datasets: [{
                        label: 'Montant des remboursements par mois',
                        data: <?= json_encode(array_column($stats['par_mois'], 'montant_total')) ?>
                    }]
                }
            });
        </script>
    </div>
</body>
</html>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
