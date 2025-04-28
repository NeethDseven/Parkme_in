<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

// This is a special page that doesn't use the standard header with navigation
// We'll include just the necessary CSS files
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ParkMeIn</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center my-5">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow fade-in">
                    <div class="card-header bg-success text-white text-center">
                        <h3 class="mb-0">Inscription</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="index.php?controller=auth&action=register">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="nom" name="nom" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <small class="text-muted-custom">Le mot de passe doit contenir au moins 6 caractères.</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-person-plus me-2"></i>S'inscrire
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-grid">
                                        <a href="index.php?controller=auth&action=login" class="btn btn-outline-secondary btn-lg">
                                            <i class="bi bi-arrow-left me-2"></i>Retour
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p>Vous avez déjà un compte ? 
                                    <a href="index.php?controller=auth&action=login" class="btn btn-sm btn-outline-primary">
                                        Se connecter
                                    </a>
                                </p>
                                <a href="index.php" class="btn btn-link">Retour à l'accueil</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
