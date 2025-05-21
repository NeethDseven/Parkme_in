<?php require_once 'frontend/Views/layouts/header.php'; ?>

<?php $pageTitle = 'Places disponibles - Parkme In'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2 mb-3">Places de parking disponibles</h1>
            <p class="text-muted">Trouvez et réservez facilement votre place de stationnement.</p>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Filtrer par type</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo BASE_URL; ?>/?page=parking&action=list" class="btn btn-sm <?php echo !isset($_GET['type']) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            Tous
                        </a>
                        <?php foreach($types as $type): ?>
                            <a href="<?php echo BASE_URL; ?>/?page=parking&action=list&type=<?php echo $type; ?>" 
                               class="btn btn-sm <?php echo (isset($_GET['type']) && $_GET['type'] === $type) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <?php 
                                    switch($type) {
                                        case 'standard': echo 'Standard'; break;
                                        case 'handicape': echo 'PMR'; break;
                                        case 'electrique': echo 'Électrique'; break;
                                        default: echo ucfirst($type);
                                    }
                                ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if(empty($places)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Aucune place disponible pour le moment.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
            <?php foreach($places as $place): ?>
                <div class="col">
                    <div class="card h-100 parking-slot available">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Place n°<?php echo $place['numero']; ?></h5>
                                <span class="badge bg-success">Disponible</span>
                            </div>
                            <ul class="list-unstyled mb-3">
                                <li class="mb-2">
                                    <i class="fas fa-tag me-2 text-muted"></i>
                                    Type: 
                                    <?php 
                                        switch($place['type']) {
                                            case 'standard': echo 'Standard'; break;
                                            case 'handicape': echo 'PMR'; break;
                                            case 'electrique': echo 'Électrique'; break;
                                            default: echo ucfirst($place['type']);
                                        }
                                    ?>
                                </li>
                            </ul>
                            
                            <div class="text-center mt-3">
                                <a href="<?php echo BASE_URL; ?>/?page=parking&action=view&id=<?php echo $place['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i> Réserver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            <?php echo $paginationLinks; ?>
        </div>
        
        <!-- Informations supplémentaires -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Informations</h5>
                <p class="card-text">
                    <?php echo $totalItems; ?> places disponibles au total.
                    <?php if(isset($typeFilter) && $typeFilter): ?>
                        Filtre actif: <?php echo ucfirst($typeFilter); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
