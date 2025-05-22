<?php $pageTitle = 'Places disponibles'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">
        <i class="fas fa-parking text-primary me-2"></i>
        Places disponibles
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

    <div class="row mb-4">
        <div class="col-md-8">
            <form id="filter-form" class="d-flex align-items-center gap-3">
                <div class="form-group">
                    <select id="type-filter" name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <?php foreach($types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= ($typeFilter == $type) ? 'selected' : '' ?>>
                                <?= ucfirst(htmlspecialchars($type)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button id="reset-filter" type="button" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Réinitialiser
                </button>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <p class="mb-0 fw-bold">
                <span id="places-count"><?= count($places) ?></span> places trouvées
            </p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach($places as $place): ?>
            <div class="col place-card" data-type="<?= htmlspecialchars($place['type']) ?>">
                <div class="card h-100 shadow-sm hover-shadow">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-parking me-2 text-primary"></i>
                                Place n°<?= htmlspecialchars($place['numero']) ?>
                            </h5>
                            <span class="badge rounded-pill 
                                <?= $place['type'] === 'standard' ? 'bg-secondary' : 
                                   ($place['type'] === 'handicape' ? 'bg-primary' : 'bg-success') ?>">
                                <?= ucfirst(htmlspecialchars($place['type'])) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Tarif horaire</span>
                                <span class="fw-bold"><?= number_format($place['prix_heure'], 2) ?> €/h</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Tarif journalier</span>
                                <span class="fw-bold"><?= number_format($place['prix_journee'], 2) ?> €/jour</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Statut</span>
                                <?php if($place['status'] === 'libre'): ?>
                                    <span class="badge bg-success">Disponible</span>
                                <?php elseif($place['status'] === 'occupe'): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock me-1"></i>Occupée actuellement
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">En maintenance</span>
                                <?php endif; ?>
                            </li>
                        </ul>
                        
                        <?php if (!empty($place['prochaines_reservations'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-calendar-alt me-1"></i> Périodes réservées :</h6>
                            <ul class="list-unstyled small">
                                <?php foreach($place['prochaines_reservations'] as $reservation): ?>
                                <li class="mb-1 text-danger">
                                    <i class="fas fa-calendar-times me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?> - 
                                    <?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid">
                            <a href="<?= BASE_URL ?>/?page=parking&action=view&id=<?= $place['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-calendar-check me-1"></i> Réserver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($places)): ?>
        <div class="alert alert-info mt-4">
            <i class="fas fa-info-circle me-2"></i>
            Aucune place disponible correspondant à vos critères.
        </div>
    <?php endif; ?>

    <div class="mt-4 d-flex justify-content-center">
        <?= $paginationLinks ?>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
