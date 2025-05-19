<?php $pageTitle = 'Ajouter une réservation - Administration Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h1 class="h3 mb-0">Ajouter une réservation</h1>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Client</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Sélectionnez un client</option>
                                <?php foreach($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= isset($_POST['user_id']) && $_POST['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['nom']) ?> <?= htmlspecialchars($user['prenom']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner un client.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="place_id" class="form-label">Place de parking</label>
                            <select class="form-select" id="place_id" name="place_id" required <?= empty($places) ? 'disabled' : '' ?>>
                                <?php if (empty($places)): ?>
                                    <option value="">Aucune place disponible</option>
                                <?php else: ?>
                                    <option value="">Sélectionnez une place</option>
                                    <?php foreach($places as $place): ?>
                                        <option value="<?= $place['id'] ?>" <?= isset($_POST['place_id']) && $_POST['place_id'] == $place['id'] ? 'selected' : '' ?>>
                                            N°<?= htmlspecialchars($place['numero']) ?> (<?= ucfirst(htmlspecialchars($place['type'])) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner une place de parking.</div>
                            <?php if (empty($places)): ?>
                                <div class="form-text text-danger">Toutes les places sont actuellement occupées.</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_debut" class="form-label">Date et heure de début</label>
                                <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" 
                                       value="<?= $_POST['date_debut'] ?? date('Y-m-d\TH:i') ?>" required>
                                <div class="invalid-feedback">Veuillez indiquer la date et l'heure de début.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_fin" class="form-label">Date et heure de fin</label>
                                <input type="datetime-local" class="form-control" id="date_fin" name="date_fin" 
                                       value="<?= $_POST['date_fin'] ?? date('Y-m-d\TH:i', strtotime('+1 day')) ?>" required>
                                <div class="invalid-feedback">Veuillez indiquer la date et l'heure de fin.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_attente" value="en_attente" 
                                       <?= (!isset($_POST['status']) || $_POST['status'] === 'en_attente') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status_attente">
                                    En attente de paiement
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_confirmee" value="confirmée"
                                       <?= (isset($_POST['status']) && $_POST['status'] === 'confirmée') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status_confirmee">
                                    Confirmée (paiement effectué)
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" <?= empty($places) ? 'disabled' : '' ?>>
                                <i class="fas fa-plus-circle me-2"></i> Créer la réservation
                            </button>
                            <a href="<?= BASE_URL ?>/?page=admin&action=reservations" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation du formulaire
(function() {
    'use strict';
    window.addEventListener('load', function() {
        // Récupérer les formulaires à valider
        var forms = document.getElementsByClassName('needs-validation');
        
        // Boucler sur les formulaires et empêcher la soumission si non valide
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                // Vérification des dates
                const dateDebut = new Date(document.getElementById('date_debut').value);
                const dateFin = new Date(document.getElementById('date_fin').value);
                
                // Vérifier que la date de fin est après la date de début
                if (dateFin <= dateDebut) {
                    event.preventDefault();
                    event.stopPropagation();
                    alert('La date de fin doit être postérieure à la date de début.');
                    return false;
                }
                
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>
