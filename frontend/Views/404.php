<?php $pageTitle = 'Page non trouvée - Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-4">
                <i class="fas fa-map-signs display-1 text-warning mb-4"></i>
                <h1 class="display-4 fw-bold mb-3">404</h1>
                <h2 class="mb-4">Page non trouvée</h2>
                <p class="lead text-muted mb-5">La page que vous recherchez n'existe pas ou a été déplacée.</p>
            </div>
            
            <div>
                <a href="<?= BASE_URL ?>/" class="btn btn-primary btn-lg px-4 me-2">
                    <i class="fas fa-home me-2"></i> Retour à l'accueil
                </a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="fas fa-arrow-left me-2"></i> Page précédente
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
