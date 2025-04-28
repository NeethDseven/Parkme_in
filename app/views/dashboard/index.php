<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container">
    <div class="row my-4">
        <div class="col-12">
            <div class="card fade-in">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <div class="welcome-message">
                        <h4 class="mb-0">Bienvenue, <?php echo htmlspecialchars($userData['prenom'] . ' ' . $userData['nom']); ?> !</h4>
                    </div>
                    <div class="nav-buttons">
                        <?php if (isset($userData['role']) && $userData['role'] === 'admin'): ?>
                        <a href="index.php?controller=admin&action=users" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-gear-fill me-1"></i> Administration
                        </a>
                        <?php endif; ?>
                        <a href="index.php?controller=dashboard&action=logout" class="btn btn-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <h2 class="section-title">Tableau de bord</h2>
                    <p>Bienvenue dans votre espace personnel ParkMeIn.</p>
                    
                    <h3 class="mt-4">Vos informations</h3>
                    <div class="accent-border-left mt-3 mb-4">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Nom:</strong> <?php echo htmlspecialchars($userData['nom']); ?></li>
                            <li class="list-group-item"><strong>Prénom:</strong> <?php echo htmlspecialchars($userData['prenom']); ?></li>
                            <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></li>
                            <li class="list-group-item"><strong>Rôle:</strong> <?php echo htmlspecialchars($userData['role']); ?></li>
                        </ul>
                    </div>
                
                    <h3 class="mt-4">Fonctionnalités</h3>
                    <div class="card bg-light-custom shadow-sm-hover">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="d-grid">
                                        <a href="index.php?controller=reservation&action=index" class="btn btn-primary">
                                            <i class="bi bi-calendar-check me-2"></i> Gérer vos réservations
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="d-grid">
                                        <a href="index.php?controller=reservation&action=history" class="btn btn-info">
                                            <i class="bi bi-clock-history me-2"></i> Consulter l'historique
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="d-grid">
                                        <a href="index.php?controller=user&action=profile" class="btn btn-secondary">
                                            <i class="bi bi-person-gear me-2"></i> Mettre à jour profil
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
