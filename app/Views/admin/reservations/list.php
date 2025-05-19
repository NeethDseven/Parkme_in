<?php $pageTitle = 'Gestion des réservations - Administration Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Gestion des réservations</h1>
            <a href="<?= BASE_URL ?>/?page=admin&action=addReservation" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Ajouter une réservation
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <!-- Filtres de recherche -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" placeholder="Rechercher par nom, email, n° place...">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="statusFilter">
                        <option value="">Tous les statuts</option>
                        <option value="confirmée">Confirmée</option>
                        <option value="en_attente">En attente</option>
                        <option value="annulée">Annulée</option>
                    </select>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="reservationsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Place</th>
                            <th>Période</th>
                            <th>Paiement</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $reservation): ?>
                        <tr>
                            <td><?= $reservation['id'] ?></td>
                            <td>
                                <div><?= htmlspecialchars($reservation['nom']) ?> <?= htmlspecialchars($reservation['prenom']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($reservation['email']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= htmlspecialchars($reservation['place_numero']) ?></span>
                                <small class="d-block mt-1"><?= ucfirst(htmlspecialchars($reservation['place_type'])) ?></small>
                            </td>
                            <td>
                                <div>Du: <?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></div>
                                <div>Au: <?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></div>
                            </td>
                            <td>
                                <?php if (isset($reservation['montant'])): ?>
                                    <div><?= number_format($reservation['montant'], 2) ?> €</div>
                                    <span class="badge <?= $reservation['payment_status'] === 'valide' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= ucfirst($reservation['payment_status'] ?? 'N/A') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Non payé</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                switch($reservation['status']) {
                                    case 'confirmée':
                                        echo '<span class="badge bg-success">Confirmée</span>';
                                        break;
                                    case 'en_attente':
                                        echo '<span class="badge bg-warning">En attente</span>';
                                        break;
                                    case 'annulée':
                                        echo '<span class="badge bg-danger">Annulée</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-secondary">'.htmlspecialchars($reservation['status']).'</span>';
                                }
                                ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/?page=admin&action=editReservation&id=<?= $reservation['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="<?= BASE_URL ?>/?page=admin&action=deleteReservation&id=<?= $reservation['id'] ?>" 
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($reservations)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                    <p>Aucune réservation trouvée</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Fonctions de filtrage et recherche
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const statusFilter = document.getElementById('statusFilter');
    const table = document.getElementById('reservationsTable');
    const rows = table.querySelectorAll('tbody tr');
    
    // Fonction de recherche
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = statusFilter.value.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const statusCell = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
            
            const matchesSearch = searchTerm === '' || text.includes(searchTerm);
            const matchesStatus = statusTerm === '' || statusCell.includes(statusTerm);
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Écouteurs d'événements
    searchButton.addEventListener('click', filterTable);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            filterTable();
        }
    });
    statusFilter.addEventListener('change', filterTable);
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>
