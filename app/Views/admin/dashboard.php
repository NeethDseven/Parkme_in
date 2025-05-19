<?php $pageTitle = 'Tableau de bord - Administration Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="h2 mb-4">Tableau de bord Administrateur</h1>
    
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-success mb-2"><?= $stats['places_libres'] ?>/<?= $stats['places_totales'] ?></div>
                    <h5 class="card-title">Places disponibles</h5>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?= ($stats['places_libres'] / $stats['places_totales']) * 100 ?>%" 
                             aria-valuenow="<?= $stats['places_libres'] ?>" 
                             aria-valuemin="0" aria-valuemax="<?= $stats['places_totales'] ?>"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-primary mb-2"><?= $stats['reservations_jour'] ?></div>
                    <h5 class="card-title">Réservations aujourd'hui</h5>
                    <p class="text-muted">Nouvelles réservations du jour</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-danger mb-2"><?= number_format($stats['revenus_mois'], 2) ?> €</div>
                    <h5 class="card-title">Revenus du mois</h5>
                    <p class="text-muted">Total des revenus pour le mois en cours</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Occupation par jour</h5>
                </div>
                <div class="card-body">
                    <canvas id="occupationChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Revenus mensuels</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/?page=admin&action=users" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-users me-2"></i> Gérer les utilisateurs
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-success w-100 py-3">
                                <i class="fas fa-parking me-2"></i> Gérer les places
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/?page=admin&action=reservations" class="btn btn-warning text-white w-100 py-3">
                                <i class="fas fa-calendar-check me-2"></i> Gérer les réservations
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/?page=admin&action=refunds" class="btn btn-info text-white w-100 py-3">
                                <i class="fas fa-exchange-alt me-2"></i> Gérer les remboursements
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Données pour les graphiques
const occupationData = <?= json_encode($stats['occupation_semaine']) ?>;
const revenueData = <?= json_encode($stats['revenus_mois_detail']) ?>;

// Configuration du graphique d'occupation
document.addEventListener('DOMContentLoaded', function() {
    const occupationChart = new Chart(
        document.getElementById('occupationChart'),
        {
            type: 'bar',
            data: {
                labels: occupationData.map(row => row.jour),
                datasets: [{
                    label: 'Places occupées',
                    data: occupationData.map(row => row.places_occupees),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
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
                    data: revenueData.map(row => row.montant),
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        }
    );
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>
