<?php $pageTitle = 'Page non trouvée'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <img src="<?= PUBLIC_URL ?>/img/404.svg" alt="Page non trouvée" class="img-fluid mb-4" style="max-height: 250px;">
            
            <h1 class="display-4 mb-4">Page non trouvée</h1>
            
            <p class="lead mb-4">
                La page que vous recherchez n'existe pas ou a été déplacée.
            </p>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="<?= BASE_URL ?>/" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Retour à l'accueil
                </a>
                <button class="btn btn-outline-secondary" onclick="window.history.back();">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la page précédente
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
