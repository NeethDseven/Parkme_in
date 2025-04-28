<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Connexion</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show">
                            <?php echo $_SESSION['flash_message']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    <?php endif; ?>
                    
                    <!-- Fix form action to use correct path -->
                    <form action="index.php?controller=auth&action=login" method="POST">
                        <div class="form-group mb-3">
                            <label for="email">Adresse email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="password">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Se connecter</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">Vous n'avez pas de compte ? 
                        <a href="index.php?controller=auth&action=register">Créer un compte</a>
                    </p>
                    <p class="mt-2 mb-0">
                        <a href="index.php?controller=auth&action=forgotPassword">Mot de passe oublié ?</a>
                    </p>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-outline-secondary">Retour à l'accueil</a>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
