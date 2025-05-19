<?php $pageTitle = 'Modifier une réservation - Administration Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h1 class="h3 mb-0">Modifier la réservation #<?= $reservation['id'] ?></h1>
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
                            <label class="form-label">Client</label>
                            <input type="text" class="form-control" 
                                   value="<?= htmlspecialchars($reservation['nom']) ?> <?= htmlspecialchars($reservation['prenom']) ?> (<?= htmlspecialchars($reservation['email']) ?>)" 
                                   readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="place_id" class="form-label">Place de parking</label>
                            <select class="form-select" id="place_id" name="place_id" required>
                                <?php foreach($places as $place): ?>
                                    <option value="<?= $place['id'] ?>" 
                                            <?= $place['id'] == $reservation['place_id'] ? 'selected' : '' ?>
                                            <?= $place['status'] === 'occupe' && $place['id'] != $reservation['place_id'] ? 'disabled' : '' ?>>
                                        N°<?= htmlspecialchars($place['numero']) ?> (<?= ucfirst(htmlspecialchars($place['type'])) ?>)
                                        <?= $place['status'] === 'occupe' && $place['id'] != $reservation['place_id'] ? '- Occupée' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner une place de parking.</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_debut" class="form-label">Date et heure de début</label>
                                <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($reservation['date_debut'])) ?>" required>
                                <div class="invalid-feedback">Veuillez indiquer la date et l'heure de début.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_fin" class="form-label">Date et heure de fin</label>
                                <input type="datetime-local" class="form-control" id="date_fin" name="date_fin" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($reservation['date_fin'])) ?>" required>
                                <div class="invalid-feedback">Veuillez indiquer la date et l'heure de fin.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <input type="hidden" name="old_status" value="<?= $reservation['status'] ?>">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_attente" value="en_attente" 
                                       <?= $reservation['status'] === 'en_attente' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status_attente">
                                    En attente de paiement
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_confirmee" value="confirmée"
                                       <?= $reservation['status'] === 'confirmée' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status_confirmee">
                                    Confirmée (paiement effectué)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_annulee" value="annulée"
                                       <?= $reservation['status'] === 'annulée' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status_annulee">
                                    Annulée
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Enregistrer les modifications
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
