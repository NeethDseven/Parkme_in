<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3><i class="fas fa-check-circle"></i> Confirmation de réservation</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <strong>Félicitations!</strong> Votre réservation a été confirmée avec succès.
            </div>
            
            <h4>Détails de la réservation :</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Numéro de réservation :</th>
                    <td><?php echo htmlspecialchars($reservation->id); ?></td>
                </tr>
                <tr>
                    <th>Place de parking :</th>
                    <td><?php echo htmlspecialchars($reservation->emplacement_id); ?></td>
                </tr>
                <tr>
                    <th>Date de début :</th>
                    <td><?php echo htmlspecialchars($reservation->date_debut); ?></td>
                </tr>
                <tr>
                    <th>Date de fin :</th>
                    <td><?php echo htmlspecialchars($reservation->date_fin); ?></td>
                </tr>
                <tr>
                    <th>Véhicule :</th>
                    <td><?php echo htmlspecialchars($reservation->vehicule ?? 'Non spécifié'); ?></td>
                </tr>
                <tr>
                    <th>Statut :</th>
                    <td>
                        <span class="badge bg-success">
                            <?php echo htmlspecialchars($reservation->statut); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Prix total :</th>
                    <td><?php echo htmlspecialchars($reservation->prix ?? 0); ?> €</td>
                </tr>
                <?php if (isset($reservation->code_acces) && !empty($reservation->code_acces)): ?>
                <tr>
                    <th>Code d'accès :</th>
                    <td><strong><?php echo htmlspecialchars($reservation->code_acces); ?></strong></td>
                </tr>
                <?php endif; ?>
            </table>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Un email de confirmation a été envoyé à votre adresse email.
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo $reservationsUrl; ?>" class="btn btn-primary">
                <i class="fas fa-list"></i> Mes réservations
            </a>
            <a href="<?php echo $homeUrl; ?>" class="btn btn-secondary">
                <i class="fas fa-home"></i> Accueil
            </a>
            <a href="/projet/Parkme_in-master/index.php?controller=reservation&action=print&id=<?php echo $reservation->id; ?>" class="btn btn-info" target="_blank">
                <i class="fas fa-print"></i> Imprimer
            </a>
        </div>
    </div>
</div>
