<?php $pageTitle = 'Gestion des horaires - Administration'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des horaires d'ouverture</h1>
        <a href="<?= BASE_URL ?>/?page=admin" class="btn btn-primary">
            <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
        </a>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Horaires d'ouverture du parking</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/?page=admin&action=horaires" method="post" class="needs-validation" novalidate>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Jour</th>
                                <th>Ouverture</th>
                                <th>Fermeture</th>
                                <th>Durée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($jour = 1; $jour <= 7; $jour++): ?>
                                <tr>
                                    <td>
                                        <strong><?= $joursNoms[$jour] ?></strong>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control" id="ouverture_<?= $jour ?>" 
                                               name="ouverture_<?= $jour ?>" 
                                               value="<?= $horairesByDay[$jour]['heure_ouverture'] ?? '08:00' ?>" 
                                               required>
                                        <div class="invalid-feedback">Heure d'ouverture requise</div>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control" id="fermeture_<?= $jour ?>" 
                                               name="fermeture_<?= $jour ?>" 
                                               value="<?= $horairesByDay[$jour]['heure_fermeture'] ?? '20:00' ?>" 
                                               required>
                                        <div class="invalid-feedback">Heure de fermeture requise</div>
                                    </td>
                                    <td>
                                        <?php
                                        $ouverture = strtotime($horairesByDay[$jour]['heure_ouverture'] ?? '08:00');
                                        $fermeture = strtotime($horairesByDay[$jour]['heure_fermeture'] ?? '20:00');
                                        $dureeHeures = round(($fermeture - $ouverture) / 3600, 1);
                                        ?>
                                        <span class="badge bg-info"><?= $dureeHeures ?> heures</span>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Les horaires d'ouverture définissent les périodes pendant lesquelles les clients peuvent réserver et accéder au parking.
                </div>
                
                <div class="d-grid gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les horaires
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script pour calculer dynamiquement la durée
document.addEventListener('DOMContentLoaded', function() {
    for (let jour = 1; jour <= 7; jour++) {
        const ouvertureInput = document.getElementById(`ouverture_${jour}`);
        const fermetureInput = document.getElementById(`fermeture_${jour}`);
        
        if (ouvertureInput && fermetureInput) {
            // Fonction pour mettre à jour la durée
            const updateDuration = () => {
                const ouverture = ouvertureInput.value;
                const fermeture = fermetureInput.value;
                
                if (ouverture && fermeture) {
                    const ouvertureTime = new Date(`2023-01-01T${ouverture}`);
                    const fermetureTime = new Date(`2023-01-01T${fermeture}`);
                    
                    // Si fermeture est avant ouverture, on ajoute un jour
                    if (fermetureTime <= ouvertureTime) {
                        fermetureTime.setDate(fermetureTime.getDate() + 1);
                    }
                    
                    const durationHours = (fermetureTime - ouvertureTime) / (1000 * 60 * 60);
                    
                    // Mettre à jour l'affichage
                    const durationBadge = ouvertureInput.closest('tr').querySelector('.badge');
                    if (durationBadge) {
                        durationBadge.textContent = `${durationHours.toFixed(1)} heures`;
                    }
                }
            };
            
            // Écouter les changements sur les champs d'heure
            ouvertureInput.addEventListener('change', updateDuration);
            fermetureInput.addEventListener('change', updateDuration);
        }
    }
});
</script>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
