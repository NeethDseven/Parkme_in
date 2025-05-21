<?php $pageTitle = 'Inscription - Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title mb-0">Créer un compte</h3>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/?page=register" class="needs-validation row g-3" novalidate>
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="nom" name="nom" value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>" required>
                                <div class="invalid-feedback">
                                    Veuillez saisir votre nom.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="prenom" name="prenom" value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>" required>
                                <div class="invalid-feedback">
                                    Veuillez saisir votre prénom.
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                                <div class="invalid-feedback">
                                    Veuillez saisir une adresse email valide.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback">
                                    Veuillez saisir un mot de passe.
                                </div>
                            </div>
                            <div class="form-text">Le mot de passe doit contenir au moins 8 caractères, incluant majuscules, minuscules et chiffres.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div class="invalid-feedback">
                                    Veuillez confirmer votre mot de passe.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : '' ?>">
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="conditions" name="conditions" required>
                                <label class="form-check-label" for="conditions">
                                    J'accepte les <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">conditions générales d'utilisation</a>
                                </label>
                                <div class="invalid-feedback">
                                    Vous devez accepter les conditions pour vous inscrire.
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>
                                    S'inscrire
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white py-3 text-center">
                    <p class="mb-0">Vous avez déjà un compte ? <a href="<?= BASE_URL ?>/?page=login">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les conditions générales -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Conditions Générales d'Utilisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>1. Services proposés</h5>
                <p>Le service de réservation de parking permet aux utilisateurs de réserver une place de stationnement pour une période déterminée.</p>

                <h5>2. Inscription et compte</h5>
                <p>Pour utiliser le service, vous devez vous inscrire et fournir des informations exactes et complètes.</p>

                <h5>3. Réservations et paiements</h5>
                <p>Les réservations sont soumises à disponibilité. Le paiement est effectué en ligne de manière sécurisée.</p>

                <h5>4. Annulations et remboursements</h5>
                <p>Les demandes d'annulation doivent être effectuées au moins 24 heures avant la date de réservation pour être éligibles à un remboursement.</p>

                <h5>5. Protection des données personnelles</h5>
                <p>Nous utilisons vos données uniquement dans le cadre de notre service et conformément à notre politique de confidentialité.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
// Validation des formulaires Bootstrap
(function() {
    'use strict';
    window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                // Vérification supplémentaire des mots de passe
                var password = document.getElementById('password');
                var confirmPassword = document.getElementById('confirm_password');
                
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas");
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    confirmPassword.setCustomValidity('');
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>