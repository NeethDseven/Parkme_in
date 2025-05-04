<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <?php if (isset($success) && !empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                        <div>
                            <strong>Félicitations!</strong>
                            <p class="mb-0"><?= $success ?></p>
                            <p class="mb-0"><small>Vous allez être redirigé vers la page de connexion...</small></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow rounded fade-in">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0">Créer un compte</h2>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error) && !empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?controller=auth&action=register" method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>" required>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer votre prénom.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>" required>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer votre nom.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                            </div>
                            <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                        </div>

                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : '' ?>" placeholder="Format: 0612345678">
                            </div>
                            <div class="form-text text-muted">Facultatif, mais utile pour vous contacter en cas de problème.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un mot de passe.</div>
                                <div class="form-text text-muted">
                                    <small>Au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="invalid-feedback">Les mots de passe ne correspondent pas.</div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                J'accepte les <a href="index.php?controller=home&action=terms" target="_blank">conditions générales</a> et la <a href="index.php?controller=home&action=privacy" target="_blank">politique de confidentialité</a> <span class="text-danger">*</span>
                            </label>
                            <div class="invalid-feedback">Vous devez accepter les conditions pour vous inscrire.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus-fill me-2"></i> S'inscrire
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center bg-light">
                    <p class="mb-0">Vous avez déjà un compte ? <a href="index.php?controller=auth&action=login">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation Bootstrap
(function () {
    'use strict'
    
    // Récupérer tous les formulaires auxquels nous voulons appliquer des styles de validation Bootstrap personnalisés
    var forms = document.querySelectorAll('.needs-validation')
    
    // Boucler et empêcher la soumission
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            
            // Vérifier que les mots de passe correspondent
            var password = document.getElementById('password')
            var confirmPassword = document.getElementById('confirm_password')
            
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas')
                event.preventDefault()
                event.stopPropagation()
            } else {
                confirmPassword.setCustomValidity('')
            }
            
            form.classList.add('was-validated')
        }, false)
    })
})()

// Fonction pour vérifier la correspondance des mots de passe
document.getElementById('confirm_password').addEventListener('input', function() {
    var password = document.getElementById('password').value;
    var confirmPassword = this.value;
    
    if(password === confirmPassword) {
        this.setCustomValidity('');
    } else {
        this.setCustomValidity('Les mots de passe ne correspondent pas');
    }
});
</script>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
