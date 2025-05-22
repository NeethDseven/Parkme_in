<?php $pageTitle = 'Gestion des places'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">
        <i class="fas fa-parking text-primary me-2"></i>
        Gestion des places de parking
    </h1>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['info'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['info']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>

    <!-- Filtres de recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Filtres</h5>
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="action" value="places">
                
                <div class="col-md-4">
                    <label for="type" class="form-label">Type de place</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">Tous les types</option>
                        <?php foreach($types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $typeFilter === $type ? 'selected' : '' ?>>
                                <?= ucfirst(htmlspecialchars($type)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="status" class="form-label">Statut</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <?php foreach($statuses as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                                <?= ucfirst(htmlspecialchars($status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Filtrer
                    </button>
                    <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bouton d'ajout de place -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Liste des places (<?= count($places) ?>)</h3>
        <a href="<?= BASE_URL ?>/?page=admin&action=addPlace" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> Ajouter une place
        </a>
    </div>

    <!-- Tableau des places -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>N°</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Réservations actives</th>
                            <th>Créée le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($places) === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Aucune place trouvée</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($places as $place): ?>
                                <tr>
                                    <td><?= htmlspecialchars($place['numero']) ?></td>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?= $place['type'] === 'standard' ? 'bg-secondary' : 
                                               ($place['type'] === 'handicape' ? 'bg-primary' : 'bg-success') ?>">
                                            <?= ucfirst(htmlspecialchars($place['type'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?= $place['status'] === 'libre' ? 'bg-success' : 
                                               ($place['status'] === 'occupe' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                            <?= ucfirst(htmlspecialchars($place['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($place['has_active_reservations'] > 0): ?>
                                            <span class="badge bg-info"><?= $place['has_active_reservations'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Aucune</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($place['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-cog me-1"></i> Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="<?= BASE_URL ?>/?page=admin&action=editPlace&id=<?= $place['id'] ?>">
                                                        <i class="fas fa-edit me-1 text-primary"></i> Modifier
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" 
                                                       href="<?= BASE_URL ?>/?page=admin&action=deletePlace&id=<?= $place['id'] ?>"
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette place ?')">
                                                        <i class="fas fa-trash-alt me-1 text-danger"></i> Supprimer
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li class="dropdown-header">Changer le statut</li>
                                                <li>
                                                    <a class="dropdown-item change-status" 
                                                       href="#" 
                                                       data-id="<?= $place['id'] ?>" 
                                                       data-status="libre">
                                                        <i class="fas fa-check-circle me-1 text-success"></i> Marquer comme libre
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item change-status" 
                                                       href="#" 
                                                       data-id="<?= $place['id'] ?>" 
                                                       data-status="occupe">
                                                        <i class="fas fa-clock me-1 text-warning"></i> Marquer comme occupée
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item change-status" 
                                                       href="#" 
                                                       data-id="<?= $place['id'] ?>" 
                                                       data-status="maintenance">
                                                        <i class="fas fa-tools me-1 text-danger"></i> Mettre en maintenance
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <?= $paginationLinks ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation pour la mise en maintenance -->
<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="maintenanceModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="maintenance-warning-text">Cette action annulera toutes les réservations actives pour cette place. Êtes-vous sûr de vouloir continuer ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" id="confirm-maintenance">Confirmer</button>
            </div>
        </div>
    </div>
</div>

<!-- Script pour la gestion des changements de statut -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables pour stocker les données de la demande en cours
    let currentPlaceId = null;
    let currentStatus = null;
    
    // Référence au modal
    const maintenanceModal = new bootstrap.Modal(document.getElementById('maintenanceModal'));
    
    // Ajouter un écouteur d'événement à tous les boutons de changement de statut
    document.querySelectorAll('.change-status').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Récupérer les attributs de données
            currentPlaceId = this.getAttribute('data-id');
            currentStatus = this.getAttribute('data-status');
            
            // Si le nouveau statut est "maintenance", afficher une confirmation
            if (currentStatus === 'maintenance') {
                maintenanceModal.show();
            } else {
                // Sinon, procéder directement au changement
                changeStatus(currentPlaceId, currentStatus, false);
            }
        });
    });
    
    // Gestionnaire pour le bouton de confirmation de maintenance
    document.getElementById('confirm-maintenance').addEventListener('click', function() {
        maintenanceModal.hide();
        changeStatus(currentPlaceId, currentStatus, true);
    });
    
    // Fonction pour changer le statut via AJAX
    function changeStatus(id, status, force) {
        // Afficher un indicateur de chargement
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'alert alert-info';
        loadingDiv.innerHTML = 'Changement du statut en cours...';
        document.querySelector('.container').insertBefore(loadingDiv, document.querySelector('.container').firstChild);
        
        // Créer un objet FormData pour les données POST
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);
        if (force) {
            formData.append('force', 1);
        }
        
        // Créer une requête fetch
        fetch('<?= BASE_URL ?>/?page=admin&action=changeStatus', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Supprimer l'indicateur de chargement
            loadingDiv.remove();
            
            if (data.success) {
                // Afficher un message de succès
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Insérer l'alerte au début du conteneur
                const container = document.querySelector('.container');
                container.insertBefore(alertDiv, container.firstChild);
                
                // Recharger la page après un court délai
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else if (data.needConfirmation) {
                // Afficher le modal avec le message personnalisé
                document.getElementById('maintenance-warning-text').textContent = data.message;
                maintenanceModal.show();
            } else {
                // Afficher un message d'erreur
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Insérer l'alerte au début du conteneur
                const container = document.querySelector('.container');
                container.insertBefore(alertDiv, container.firstChild);
            }
        })
        .catch(error => {
            // Supprimer l'indicateur de chargement
            loadingDiv.remove();
            
            // Afficher l'erreur
            console.error('Erreur:', error);
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                Une erreur est survenue lors de la communication avec le serveur: ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Insérer l'alerte au début du conteneur
            const container = document.querySelector('.container');
            container.insertBefore(alertDiv, container.firstChild);
        });
    }
});
</script>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
