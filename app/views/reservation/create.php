<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Nouvelle réservation</h1>
    
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!isset($parkingId) || !$parkingId): ?>
        <!-- Step 1: Select a parking lot -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Sélectionnez un parking</h2>
            </div>
            <div class="card-body">
                <form action="index.php" method="GET">
                    <input type="hidden" name="controller" value="reservation">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-group">
                        <label for="parking_id">Parking</label>
                        <select class="form-control" id="parking_id" name="parking_id" required>
                            <option value="">Sélectionnez un parking</option>
                            <?php foreach ($parkings as $parking): ?>
                                <option value="<?= $parking['id'] ?>"><?= $parking['nom'] ?> - <?= $parking['adresse'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-3">Continuer</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Step 2: Select date and time -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Réserver une place dans <?= htmlspecialchars($parkingDetails['nom']) ?></h2>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h3 class="h6">Informations sur le parking:</h3>
                    <p>
                        <strong>Adresse:</strong> <?= htmlspecialchars($parkingDetails['adresse']) ?><br>
                        <strong>Tarif horaire:</strong> <?= number_format($parkingDetails['tarif_horaire'], 2) ?> €<br>
                        <strong>Ouverture:</strong> <?= substr($parkingDetails['ouverture'], 0, 5) ?> - <?= substr($parkingDetails['fermeture'], 0, 5) ?>
                    </p>
                </div>
                
                <!-- First select date and time -->
                <?php if (!isset($startDate) || !isset($endDate) || empty($startDate) || empty($endDate)): ?>
                <form id="date-selection-form" action="index.php" method="GET">
                    <input type="hidden" name="controller" value="reservation">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="parking_id" value="<?= $parkingId ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="start_date">Date et heure de début</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" 
                                    value="<?= isset($formData['start_date']) ? htmlspecialchars($formData['start_date']) : date('Y-m-d\TH:i') ?>" 
                                    min="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="end_date">Date et heure de fin</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" 
                                    value="<?= isset($formData['end_date']) ? htmlspecialchars($formData['end_date']) : date('Y-m-d\TH:i', strtotime('+1 hour')) ?>" 
                                    min="<?= date('Y-m-d\TH:i', strtotime('+30 minutes')) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estimation du prix -->
                    <div id="price-estimation" class="alert alert-info mt-3" style="display: none;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Prix estimé: <strong id="estimated-price">0.00</strong> €
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Rechercher des places disponibles</button>
                    </div>
                </form>
                
                <?php else: ?>
                <!-- Then select an available spot -->
                <div class="mb-3">
                    <div class="alert alert-info">
                        <h4 class="alert-heading h6">Période sélectionnée:</h4>
                        <p class="mb-0">
                            Du <?= date('d/m/Y H:i', strtotime($startDate)) ?> au <?= date('d/m/Y H:i', strtotime($endDate)) ?>
                        </p>
                        <p class="mb-0">
                            <a href="index.php?controller=reservation&action=create&parking_id=<?= $parkingId ?>" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-arrow-left me-1"></i> Changer la période
                            </a>
                        </p>
                    </div>
                </div>
                
                <?php if (empty($availableSpots)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i> 
                        Aucune place n'est disponible dans ce parking pour la période sélectionnée.
                        <p class="mt-2">Essayez de choisir une autre période ou un autre parking.</p>
                        <!-- Ajouter une information supplémentaire pour aider l'utilisateur -->
                        <p class="mt-2">
                            <small class="text-muted">
                                Note: Les heures d'ouverture du parking sont de <?= substr($parkingDetails['ouverture'], 0, 5) ?> à <?= substr($parkingDetails['fermeture'], 0, 5) ?>.
                                Assurez-vous que votre réservation se situe pendant ces heures.
                            </small>
                        </p>
                    </div>
                    <div class="mt-3">
                        <a href="index.php?controller=reservation&action=create" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-1"></i> Choisir un autre parking
                        </a>
                    </div>
                <?php else: ?>
                    <form action="index.php" method="POST" id="reservation-form">
                        <input type="hidden" name="controller" value="reservation">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="parking_id" value="<?= $parkingId ?>">
                        <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                        <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                        
                        <div class="form-group mb-3">
                            <label for="spot_id">Place de parking disponible (<?= count($availableSpots) ?> places)</label>
                            <select class="form-control" id="spot_id" name="spot_id" required>
                                <option value="">Sélectionnez une place</option>
                                <?php foreach ($availableSpots as $spot): ?>
                                    <option value="<?= $spot['id'] ?>">
                                        <?= htmlspecialchars($spot['numero']) ?> - 
                                        <?= $spot['type'] === 'normale' ? 'Standard' : 
                                            ($spot['type'] === 'handicapee' ? 'Handicapé' : 'Réservée') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="vehicle">Véhicule (optionnel)</label>
                            <input type="text" class="form-control" id="vehicle" name="vehicle" 
                                placeholder="Ex: Renault Clio - AB-123-CD" 
                                value="<?= isset($formData['vehicle']) ? htmlspecialchars($formData['vehicle']) : '' ?>">
                        </div>
                        
                        <!-- Estimation du prix final -->
                        <?php
                        // Calcul du prix estimé
                        $startTimestamp = strtotime($startDate);
                        $endTimestamp = strtotime($endDate);
                        $diffHours = ($endTimestamp - $startTimestamp) / 3600;
                        $estimatedPrice = $diffHours * $parkingDetails['tarif_horaire'];
                        ?>
                        <div class="alert alert-info mb-4">
                            <h5 class="h6">Résumé de la réservation:</h5>
                            <p class="mb-1">Durée: <?= floor($diffHours) ?>h<?= ($diffHours * 60) % 60 ? sprintf(' %02d min', ($diffHours * 60) % 60) : '' ?></p>
                            <p class="mb-1">Prix total: <strong><?= number_format($estimatedPrice, 2, ',', ' ') ?> €</strong></p>
                            <p class="mb-0 small text-muted">En cliquant sur "Réserver", vous acceptez les conditions générales de réservation.</p>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success" id="submit-reservation">
                                <i class="bi bi-check-circle me-1"></i> Réserver
                            </button>
                        </div>
                    </form>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const form = document.getElementById('reservation-form');
                        if (form) {
                            form.addEventListener('submit', function(e) {
                                // Ajout d'une petite vérification côté client
                                const spotId = document.getElementById('spot_id').value;
                                if (!spotId) {
                                    e.preventDefault();
                                    alert('Veuillez sélectionner une place de parking.');
                                    return false;
                                }
                                
                                // Désactiver le bouton pour éviter les soumissions multiples
                                const submitBtn = document.getElementById('submit-reservation');
                                submitBtn.disabled = true;
                                submitBtn.innerHTML = '<i class="bi bi-hourglass me-1"></i> Traitement en cours...';
                                
                                // Log pour debug
                                console.log('Formulaire soumis avec: ', {
                                    parking_id: <?= $parkingId ?>,
                                    spot_id: spotId,
                                    start_date: '<?= addslashes($startDate) ?>',
                                    end_date: '<?= addslashes($endDate) ?>'
                                });
                            });
                        }
                    });
                    </script>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get elements
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const priceEstimation = document.getElementById('price-estimation');
            const estimatedPrice = document.getElementById('estimated-price');
            const hourlyRate = <?= $parkingDetails['tarif_horaire'] ?>;
            
            // Function to calculate and display estimated price
            function updatePriceEstimation() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                
                if (!isNaN(startDate) && !isNaN(endDate) && startDate < endDate) {
                    // Calculate hours difference
                    const diffMs = endDate - startDate;
                    const diffHours = diffMs / (1000 * 60 * 60);
                    
                    // Calculate price
                    const price = diffHours * hourlyRate;
                    estimatedPrice.textContent = price.toFixed(2);
                    priceEstimation.style.display = 'block';
                } else {
                    priceEstimation.style.display = 'none';
                }
            }
            
            // Add event listeners
            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', updatePriceEstimation);
                endDateInput.addEventListener('change', updatePriceEstimation);
                // Execute on page load if we have values
                updatePriceEstimation();
            }
        });
        </script>
    <?php endif; ?>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>

