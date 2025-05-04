<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow text-center fade-in">
                <div class="card-body">
                    <h1 class="display-1 text-danger"><?= $code ?></h1>
                    <h2 class="h3 mb-3"><?= htmlspecialchars($title) ?></h2>
                    <p class="lead mb-4"><?= htmlspecialchars($message) ?></p>
                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 5rem; color: #dc3545;"></i>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-primary">
                            <i class="bi bi-house-door me-2"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
