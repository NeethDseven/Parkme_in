<?php $pageTitle = 'Réserver la place n°' . $place['numero']; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=parking&action=list">Places disponibles</a></li>
            <li class="breadcrumb-item active">Place n°<?= htmlspecialchars($place['numero']) ?></li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-parking me-2"></i>
                        Place n°<?= htmlspecialchars($place['numero']) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center mb-4">
                        <?php
                        // Afficher l'icône en fonction du type de place
                        switch ($place['type']) {
                            case 'handicape':
                                echo '<div class="place-icon handicape"><i class="fas fa-wheelchair fa-3x"></i></div>';
                                break;
                            case 'electrique':
                                echo '<div class="place-icon electrique"><i class="fas fa-charging-station fa-3x"></i></div>';
                                break;
                            default:
                                echo '<div class="place-icon standard"><i class="fas fa-car fa-3x"></i></div>';
                        }
                        ?>
                    </div>
                    
                    <h5 class="fw-bold mb-3">Détails de la place</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Type
                            <span class="badge rounded-pill 
                                <?= $place['type'] === 'standard' ? 'bg-secondary' : 
                                   ($place['type'] === 'handicape' ? 'bg-primary' : 'bg-success') ?>">
                                <?= ucfirst(htmlspecialchars($place['type'])) ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tarif horaire
                            <span class="badge bg-info"><?= number_format($place['prix_heure'], 2) ?> €/h</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tarif journalier
                            <span class="badge bg-info"><?= number_format($place['prix_journee'], 2) ?> €/jour</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Statut actuel
                            <?php if($place['status'] === 'libre'): ?>
                                <span class="badge bg-success">Disponible maintenant</span>
                            <?php elseif($place['status'] === 'occupe'): ?>
                                <span class="badge bg-warning text-dark">Occupée actuellement</span>
                            <?php else: ?>
                                <span class="badge bg-danger">En maintenance</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Vous pouvez réserver cette place à tout moment, même si elle est actuellement occupée ou réservée à d'autres moments.
                        Si un créneau est indisponible, vous pourrez automatiquement créer une alerte pour être notifié dès qu'il se libère.
                    </div>
                    
                    <?php if (!empty($alertesUtilisateur)): ?>
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Vos alertes pour cette place</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach($alertesUtilisateur as $alerte): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <small>Du <?= date('d/m/Y H:i', strtotime($alerte['date_debut'])) ?></small><br>
                                        <small>Au <?= date('d/m/Y H:i', strtotime($alerte['date_fin'])) ?></small>
                                    </div>
                                    <button 
                                        class="btn btn-sm btn-outline-danger delete-alert-btn" 
                                        data-alerte-id="<?= $alerte['id'] ?>"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Bouton pour créer une alerte si aucun créneau ne convient -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-warning">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bell me-2"></i>
                                Créer une alerte
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Aucun créneau ne vous convient ? Créez une alerte pour être notifié si un créneau se libère.</p>
                            <p class="card-text text-muted small">Une alerte sera automatiquement proposée si vous sélectionnez un créneau déjà réservé.</p>
                            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#alertModal">
                                <i class="fas fa-bell me-2"></i>Créer une alerte manuelle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Réserver cette place
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Formulaire intégré avec le calendrier -->
                    <form 
                        method="post" 
                        class="reservation-form" 
                        data-tarif-horaire="<?= $place['prix_heure'] ?>"
                        data-tarif-journee="<?= $place['prix_journee'] ?>"
                    >
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Sélectionnez vos dates de réservation dans le calendrier</h6>
                            
                            <!-- Calendrier interactif (maintenant principal) -->
                            <div id="reservation-calendar" class="mb-3"></div>
                            
                            <div class="d-flex justify-content-center mb-3">
                                <div class="legend d-flex align-items-center me-3">
                                    <div class="legend-color bg-success me-1" style="width:15px;height:15px;"></div>
                                    <small>Disponible</small>
                                </div>
                                <div class="legend d-flex align-items-center me-3">
                                    <div class="legend-color" style="width:15px;height:15px;background-color:rgba(255, 165, 0, 0.3);"></div>
                                    <small>Partiellement réservé</small>
                                </div>
                                <div class="legend d-flex align-items-center">
                                    <div class="legend-color bg-danger me-1" style="width:15px;height:15px;"></div>
                                    <small>Indisponible</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Champs masqués pour stocker les valeurs -->
                        <input type="hidden" name="date_debut" id="date_debut" required>
                        <input type="hidden" name="date_fin" id="date_fin" required>
                        
                        <!-- Affichage des dates et heures sélectionnées -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <small class="text-muted d-block">Date et heure de début:</small>
                                        <span id="date_debut_display" class="fw-bold">Non sélectionné</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <small class="text-muted d-block">Date et heure de fin:</small>
                                        <span id="date_fin_display" class="fw-bold">Non sélectionné</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sélecteurs d'heures -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="heure_debut" class="form-label">Heure de début</label>
                                <select id="heure_debut" class="form-select">
                                    <?php for($h = 0; $h < 24; $h++): ?>
                                        <?php for($m = 0; $m < 60; $m += 30): ?>
                                            <option value="<?= sprintf('%02d:%02d', $h, $m) ?>">
                                                <?= sprintf('%02d:%02d', $h, $m) ?>
                                            </option>
                                        <?php endfor; ?>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="heure_fin" class="form-label">Heure de fin</label>
                                <select id="heure_fin" class="form-select">
                                    <?php for($h = 0; $h < 24; $h++): ?>
                                        <?php for($m = 0; $m < 60; $m += 30): ?>
                                            <option value="<?= sprintf('%02d:%02d', $h, $m) ?>" <?= ($h == 18 && $m == 0) ? 'selected' : '' ?>>
                                                <?= sprintf('%02d:%02d', $h, $m) ?>
                                            </option>
                                        <?php endfor; ?>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-4" id="prix_container">
                            <strong>Prix estimé: </strong>
                            <span id="prix_estime">Veuillez sélectionner les dates</span>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="submit-reservation" disabled>
                                <i class="fas fa-check-circle me-2"></i>Réserver maintenant
                            </button>
                            
                            <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour aux places disponibles
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour créer une alerte -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="alertModalLabel">Créer une alerte de disponibilité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="alert-form">
                    <input type="hidden" name="place_id" value="<?= $place['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="alert_date_debut" class="form-label">Date et heure de début souhaitées</label>
                        <input type="datetime-local" class="form-control" id="alert_date_debut" name="date_debut" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alert_date_fin" class="form-label">Date et heure de fin souhaitées</label>
                        <input type="datetime-local" class="form-control" id="alert_date_fin" name="date_fin" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Vous recevrez une notification si ce créneau devient disponible.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" id="create-alert-btn">
                    <i class="fas fa-bell me-1"></i> Créer l'alerte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Passer les créneaux indisponibles au JavaScript -->
<script>
    var creneauxIndisponibles = <?= json_encode($creneauxIndisponibles) ?>;
</script>

<!-- Scripts supplémentaires -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
<script src="<?= PUBLIC_URL ?>/js/reservation-calendar.js"></script>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
