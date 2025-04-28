<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

// Include the main header
include_once BASE_PATH . '/app/views/includes/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impression de réservation #<?php echo htmlspecialchars($reservation->id); ?></title>
    <link rel="stylesheet" href="/projet/Parkme_in-master/public/css/bootstrap.min.css">
    <style>
        @media print {
            .navbar, 
            .btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold">ParkMeIn</h2>
            <p class="lead">Confirmation de réservation de parking</p>
            <hr class="my-4">
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>Confirmation de réservation #<?php echo htmlspecialchars($reservation->id); ?></h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 35%">Numéro de réservation</th>
                        <td><?php echo htmlspecialchars($reservation->id); ?></td>
                    </tr>
                    <tr>
                        <th>Place de parking</th>
                        <td><?php echo htmlspecialchars($reservation->emplacement_id); ?></td>
                    </tr>
                    <tr>
                        <th>Date de début</th>
                        <td><?php echo htmlspecialchars($reservation->date_debut); ?></td>
                    </tr>
                    <tr>
                        <th>Date de fin</th>
                        <td><?php echo htmlspecialchars($reservation->date_fin); ?></td>
                    </tr>
                    <tr>
                        <th>Véhicule</th>
                        <td><?php echo htmlspecialchars($reservation->vehicule ?? 'Non spécifié'); ?></td>
                    </tr>
                    <tr>
                        <th>Statut</th>
                        <td><span class="badge bg-success"><?php echo htmlspecialchars($reservation->statut); ?></span></td>
                    </tr>
                    <tr>
                        <th>Prix total</th>
                        <td><?php echo htmlspecialchars($reservation->prix ?? 0); ?> €</td>
                    </tr>
                    <?php if (isset($reservation->code_acces) && !empty($reservation->code_acces)): ?>
                    <tr>
                        <th>Code d'accès</th>
                        <td><strong><?php echo htmlspecialchars($reservation->code_acces); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="/projet/Parkme_in-master/index.php?controller=reservation&action=index" class="btn btn-secondary">Retour aux réservations</a>
                    <button onclick="window.print();" class="btn btn-primary">Imprimer cette page</button>
                </div>
                <div class="mt-3 text-center text-muted">
                    <small>Merci d'avoir choisi ParkMeIn pour votre stationnement. Pour toute question, contactez notre service client.</small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/projet/Parkme_in-master/public/js/bootstrap.bundle.min.js"></script>
    <script>
        // Automatically trigger print dialog when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
