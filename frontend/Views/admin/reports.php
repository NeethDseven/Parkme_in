<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="admin-container">
    <h1>Rapports et statistiques</h1>
    
    <div class="report-section">
        <h2>Revenus mensuels</h2>
        <canvas id="revenueChart" width="400" height="200"></canvas>
    </div>
    
    <div class="report-section">
        <h2>Occupation par type de place</h2>
        <div class="occupation-stats">
            <?php foreach($occupationParType as $stat): ?>
                <div class="stat-card">
                    <h3><?= ucfirst(htmlspecialchars($stat['type'])) ?></h3>
                    <div class="progress-bar">
                        <?php 
                        $tauxOccupation = $stat['total'] > 0 ? 
                            round(($stat['occupees'] / $stat['total']) * 100) : 0; 
                        ?>
                        <div class="progress" style="width: <?= $tauxOccupation ?>%;"><?= $tauxOccupation ?>%</div>
                    </div>
                    <p><?= $stat['occupees'] ?> / <?= $stat['total'] ?> places occupées</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="report-section">
        <h2>Réservations par jour</h2>
        <canvas id="reservationsChart" width="400" height="200"></canvas>
    </div>
</div>

<script>
    // Données pour les graphiques
    const revenueData = {
        labels: <?= json_encode(array_column($revenusMensuels, 'mois')) ?>,
        datasets: [{
            label: 'Revenus mensuels (€)',
            data: <?= json_encode(array_column($revenusMensuels, 'total')) ?>,
            backgroundColor: 'rgba(52, 152, 219, 0.5)',
            borderColor: 'rgba(52, 152, 219, 1)',
            borderWidth: 1
        }]
    };
    
    const reservationsData = {
        labels: <?= json_encode(array_column($reservationsParJour, 'jour')) ?>,
        datasets: [{
            label: 'Nombre de réservations',
            data: <?= json_encode(array_column($reservationsParJour, 'nombre')) ?>,
            backgroundColor: 'rgba(155, 89, 182, 0.5)',
            borderColor: 'rgba(155, 89, 182, 1)',
            borderWidth: 1
        }]
    };
    
    // Initialisation des graphiques
    window.addEventListener('DOMContentLoaded', () => {
        // Revenus mensuels
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: revenueData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Réservations par jour
        const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
        new Chart(reservationsCtx, {
            type: 'bar',
            data: reservationsData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
