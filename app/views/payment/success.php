<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow fade-in">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <div class="bg-success text-white d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-check-lg" style="font-size: 2.5rem;"></i>
                        </div>
                        <h1 class="h3">Paiement confirmé !</h1>
                        <p class="lead">Votre réservation a été confirmée avec succès.</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-8 offset-md-2">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h2 class="h5 mb-3">Détails de la réservation</h2>
                                    
                                    <table class="table table-borderless">
                                        <tr>
                                            <th class="text-start">N° de réservation:</th>
                                            <td class="text-end"><?= $reservation->id ?></td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Parking:</th>
                                            <td class="text-end"><?= htmlspecialchars($reservation->parking_nom) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Place de parking:</th>
                                            <td class="text-end"><?= htmlspecialchars($reservation->numero_place) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Date de début:</th>
                                            <td class="text-end"><?= date('d/m/Y H:i', strtotime($reservation->date_debut)) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Date de fin:</th>
                                            <td class="text-end"><?= date('d/m/Y H:i', strtotime($reservation->date_fin)) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Montant payé:</th>
                                            <td class="text-end"><strong class="text-success"><?= number_format($payment['montant'], 2, ',', ' ') ?> €</strong></td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Code d'accès:</th>
                                            <td class="text-end">
                                                <span class="badge bg-primary p-2"><?= $reservation->code_acces ?></span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Veuillez présenter ce code lors de votre arrivée au parking.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="index.php?controller=reservation&action=print&id=<?= $reservation->id ?>" 
                           class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-printer me-1"></i> Imprimer
                        </a>
                        <a href="index.php?controller=reservation&action=index" class="btn btn-primary">
                            <i class="bi bi-calendar-check me-1"></i> Mes réservations
                        </a>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">
                        Un email de confirmation a été envoyé à votre adresse email.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
