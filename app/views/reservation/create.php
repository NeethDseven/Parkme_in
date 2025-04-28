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
                <form action="index.php?controller=reservation&action=create" method="GET">
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
                    
                    <button type="submit" class="btn btn-primary">Continuer</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Step 2: Select a spot and time -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Réserver une place dans <?= $parkingDetails['nom'] ?></h2>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h3 class="h6">Informations sur le parking:</h3>
                    <p>
                        <strong>Adresse:</strong> <?= $parkingDetails['adresse'] ?><br>
                        <strong>Tarif horaire:</strong> <?= number_format($parkingDetails['tarif_horaire'], 2) ?> €<br>
                        <strong>Nombre de places disponibles:</strong> <?= count($parkingSpots) ?>
                    </p>
                </div>
                
                <form action="index.php?controller=reservation&action=create&parking_id=<?= $parkingId ?>" method="POST">
                    <div class="form-group mb-3">
                        <label for="spot_id">Place de parking</label>
                        <select class="form-control" id="spot_id" name="spot_id" required>
                            <option value="">Sélectionnez une place</option>
                            <?php foreach ($parkingSpots as $spot): ?>
                                <option value="<?= $spot['id'] ?>" <?= isset($formData['spot_id']) && $formData['spot_id'] == $spot['id'] ? 'selected' : '' ?>>
                                    <?= $spot['numero'] ?> (<?= $spot['type'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="start_date">Date et heure de début</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" 
                               value="<?= isset($formData['start_date']) ? $formData['start_date'] : '' ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="end_date">Date et heure de fin</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" 
                               value="<?= isset($formData['end_date']) ? $formData['end_date'] : '' ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="vehicle">Véhicule (marque, modèle et plaque d'immatriculation)</label>
                        <input type="text" class="form-control" id="vehicle" name="vehicle" 
                               value="<?= isset($formData['vehicle']) ? htmlspecialchars($formData['vehicle']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <div id="price-estimation" class="alert alert-info" style="display: none;">
                            Prix estimé: <span id="estimated-price">0.00</span> €
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
                    <a href="index.php?controller=reservation&action=create" class="btn btn-secondary">Retour</a>
                </form>
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
            startDateInput.addEventListener('change', updatePriceEstimation);
            endDateInput.addEventListener('change', updatePriceEstimation);
            
            // Initialize
            updatePriceEstimation();
        });
        </script>
    <?php endif; ?>
</div>

