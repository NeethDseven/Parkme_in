<?php require_once 'app/Views/layouts/header.php'; ?>

<?php $pageTitle = 'Accueil - Parkme In'; ?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-8">
            <div class="mb-4">
                <h1 class="display-4 fw-bold">Bienvenue sur Parkme In</h1>
                <p class="lead text-secondary">La solution simple pour trouver et réserver votre place de parking.</p>
            </div>
            
            <div class="d-grid gap-2 d-md-flex mt-4">
                <a href="<?php echo BASE_URL; ?>/?page=parking&action=list" class="btn btn-primary btn-lg">
                    <i class="fas fa-search me-2"></i> Trouver une place
                </a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_URL; ?>/?page=register" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-user-plus me-2"></i> Créer un compte
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-center justify-content-center">
            <div class="card bg-light text-center p-4 w-100">
                <h3 class="mb-3">Places disponibles</h3>
                <div class="display-1 fw-bold text-primary"><?php echo $stats['places_libres']; ?></div>
                <p class="text-muted">sur <?php echo $stats['places_totales']; ?> places au total</p>
                <div class="progress mt-2" style="height: 10px;">
                    <div class="progress-bar bg-primary" role="progressbar" 
                         style="width: <?php echo ($stats['places_libres'] / $stats['places_totales']) * 100; ?>%"
                         aria-valuenow="<?php echo $stats['places_libres']; ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="<?php echo $stats['places_totales']; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row my-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">Nos services</h2>
            <p class="text-muted">Découvrez comment nous pouvons vous faciliter la vie</p>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-search fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Recherche facile</h5>
                    <p class="card-text">Trouvez rapidement une place de parking disponible près de votre destination.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-calendar-alt fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Réservation en ligne</h5>
                    <p class="card-text">Réservez votre place à l'avance et assurez-vous d'avoir un emplacement garanti.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-credit-card fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Paiement sécurisé</h5>
                    <p class="card-text">Payez en toute sécurité avec notre système de paiement en ligne intégré.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5 py-4 bg-light rounded">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">Comment ça marche ?</h2>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <span class="h4 m-0">1</span>
                    </div>
                    <h5 class="card-title">Créez un compte</h5>
                    <p class="card-text small">Inscrivez-vous rapidement et gratuitement.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <span class="h4 m-0">2</span>
                    </div>
                    <h5 class="card-title">Trouvez une place</h5>
                    <p class="card-text small">Consultez les places disponibles.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <span class="h4 m-0">3</span>
                    </div>
                    <h5 class="card-title">Réservez</h5>
                    <p class="card-text small">Choisissez votre place et réservez-la.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <span class="h4 m-0">4</span>
                    </div>
                    <h5 class="card-title">Payez</h5>
                    <p class="card-text small">Réglez en ligne de manière sécurisée.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>
