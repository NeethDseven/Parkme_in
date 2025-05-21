<?php $pageTitle = 'Erreur - Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-4">
                <i class="fas fa-exclamation-triangle text-danger display-1 mb-4"></i>
                <h1 class="display-4 fw-bold">Oops!</h1>
                <h2 class="mb-4">Une erreur est survenue</h2>
                <p class="lead text-muted mb-5">
                    Nous sommes désolés pour ce désagrément. Notre équipe technique a été informée du problème.
                </p>
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
