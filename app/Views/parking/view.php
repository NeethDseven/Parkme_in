<?php $pageTitle = 'Réservation de place - Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h1 class="h3 mb-0">Réservation de la place n°<?= htmlspecialchars($place['numero']) ?></h1>
                </div>
                
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Détails de la place</h5>
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Type:</span>
                                            <span class="badge bg-primary">
                                                <?php 
                                                switch($place['type']) {
                                                    case 'standard': echo 'Standard'; break;
                                                    case 'handicape': echo 'PMR'; break;
                                                    case 'electrique': echo 'Électrique'; break;
                                                    default: echo ucfirst(htmlspecialchars($place['type']));
                                                }
                                                ?>
                                            </span>
                                        </li>
                                        <li class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Tarif horaire:</span>
                                            <span class="fw-bold"><?= number_format($place['prix_heure'], 2) ?> €/h</span>
                                        </li>
                                        <li class="d-flex justify-content-between align-items-center">
                                            <span>Tarif journalier:</span>
                                            <span class="fw-bold"><?= number_format($place['prix_journee'], 2) ?> €/jour</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="date_debut" class="form-label">Date et heure de début</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" required 
                                               min="<?= date('Y-m-d\TH:i') ?>">
                                        <div class="invalid-feedback">
                                            Veuillez indiquer une date et heure de début valide.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="date_fin" class="form-label">Date et heure de fin</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="datetime-local" class="form-control" id="date_fin" name="date_fin" required 
                                               min="<?= date('Y-m-d\TH:i') ?>">
                                        <div class="invalid-feedback">
                                            Veuillez indiquer une date et heure de fin valide.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mb-4" id="prix_container">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Prix estimé:</span>
                                        <span class="h4 mb-0" id="prix_estime">-- €</span>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-check-circle me-2"></i> Réserver maintenant
                                    </button>
                                    <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i> Retour à la liste
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title">Informations importantes</h5>
                    <ul class="mb-0">
                        <li>Votre réservation n'est pas confirmée tant que le paiement n'est pas effectué.</li>
                        <li>Le parking est accessible 24h/24 et 7j/7 avec votre code de réservation.</li>
                        <li>En cas d'annulation moins de 24h avant votre réservation, des frais peuvent s'appliquer.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const prixEstime = document.getElementById('prix_estime');
    const prixContainer = document.getElementById('prix_container');
    const tarifHoraire = <?= $place['prix_heure'] ?>;
    const tarifJournalier = <?= $place['prix_journee'] ?>;
    
    function calculerPrix() {
        if (!dateDebut.value || !dateFin.value) return;
        
        const debut = new Date(dateDebut.value);
        const fin = new Date(dateFin.value);
        
        if (fin <= debut) {
            prixEstime.textContent = "Date de fin invalide";
            prixContainer.classList.remove('alert-info');
            prixContainer.classList.add('alert-danger');
            return;
        }
        
        const dureeHeures = (fin - debut) / 1000 / 60 / 60;
        let prix;
        
        if (dureeHeures <= 24) {
            prix = dureeHeures * tarifHoraire;
        } else {
            prix = Math.ceil(dureeHeures / 24) * tarifJournalier;
        }
        
        prixContainer.classList.remove('alert-danger');
        prixContainer.classList.add('alert-info');
        prixEstime.textContent = prix.toFixed(2) + " €";
    }
    
    dateDebut.addEventListener('change', calculerPrix);
    dateFin.addEventListener('change', calculerPrix);
    
    // Form validation avec Bootstrap
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        const debut = new Date(dateDebut.value);
        const fin = new Date(dateFin.value);
        
        if (fin <= debut) {
            event.preventDefault();
            prixEstime.textContent = "La date de fin doit être postérieure à la date de début";
            prixContainer.classList.remove('alert-info');
            prixContainer.classList.add('alert-danger');
        }
        
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>
