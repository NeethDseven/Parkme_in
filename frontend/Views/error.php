<?php $pageTitle = 'Erreur'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h1 class="h4 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Une erreur est survenue
                    </h1>
                </div>
                <div class="card-body">
                    <p class="lead">
                        <?php if (isset($_SESSION['error'])): ?>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <?php unset($_SESSION['error']); ?>
                        <?php else: ?>
                            Une erreur inattendue s'est produite. Veuillez réessayer ultérieurement.
                        <?php endif; ?>
                    </p>
                    
                    <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center">
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
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
