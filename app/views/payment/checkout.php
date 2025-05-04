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
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">Paiement de réservation</h1>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h4 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Erreur de paiement</h4>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="h4 mb-3 accent-border-left">Détails de la réservation</h2>
                            
                            <table class="table table-bordered">
                                <tr>
                                    <th>Réservation N°</th>
                                    <td><?= $reservation->id ?></td>
                                </tr>
                                <tr>
                                    <th>Parking</th>
                                    <td><?= htmlspecialchars($reservation->parking_nom) ?></td>
                                </tr>
                                <tr>
                                    <th>Place N°</th>
                                    <td><?= htmlspecialchars($reservation->numero_place) ?></td>
                                </tr>
                                <tr>
                                    <th>Date de début</th>
                                    <td><?= date('d/m/Y H:i', strtotime($reservation->date_debut)) ?></td>
                                </tr>
                                <tr>
                                    <th>Date de fin</th>
                                    <td><?= date('d/m/Y H:i', strtotime($reservation->date_fin)) ?></td>
                                </tr>
                                <tr>
                                    <th>Montant total</th>
                                    <td><strong class="text-primary"><?= number_format($reservation->prix, 2, ',', ' ') ?> €</strong></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h2 class="h4 mb-3 accent-border-left">Informations de paiement</h2>
                            
                            <form action="index.php?controller=payment&action=process" method="POST">
                                <input type="hidden" name="reservation_id" value="<?= $reservation->id ?>">
                                
                                <div class="form-group mb-3">
                                    <label class="form-label">Méthode de paiement</label>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="card_payment" value="carte" checked>
                                        <label class="form-check-label" for="card_payment">
                                            <i class="bi bi-credit-card me-1"></i> Carte bancaire
                                        </label>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="paypal_payment" value="paypal">
                                        <label class="form-check-label" for="paypal_payment">
                                            <i class="bi bi-paypal me-1"></i> PayPal
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="card_details">
                                    <div class="form-group mb-3">
                                        <label for="card_number">Numéro de carte</label>
                                        <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="16">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="card_expiry">Date d'expiration</label>
                                                <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="card_cvc">Code CVC</label>
                                                <input type="text" class="form-control" id="card_cvc" name="card_cvc" placeholder="123" maxlength="4">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="card_name">Nom sur la carte</label>
                                        <input type="text" class="form-control" id="card_name" name="card_name" placeholder="John Doe">
                                    </div>
                                </div>
                                
                                <div id="paypal_details" style="display: none;">
                                    <div class="alert alert-info">
                                        <p class="mb-0">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Vous allez être redirigé vers le site de PayPal pour effectuer le paiement.
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-lock me-1"></i> Payer <?= number_format($reservation->prix, 2, ',', ' ') ?> €
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php?controller=reservation&action=view&id=<?= $reservation->id ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Retour à la réservation
                        </a>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="bi bi-shield-lock me-1"></i>
                            Paiement sécurisé SSL. Vos informations de paiement sont protégées.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cardPayment = document.getElementById('card_payment');
    const paypalPayment = document.getElementById('paypal_payment');
    const cardDetails = document.getElementById('card_details');
    const paypalDetails = document.getElementById('paypal_details');
    
    function togglePaymentMethod() {
        if (cardPayment.checked) {
            cardDetails.style.display = 'block';
            paypalDetails.style.display = 'none';
        } else {
            cardDetails.style.display = 'none';
            paypalDetails.style.display = 'block';
        }
    }
    
    cardPayment.addEventListener('change', togglePaymentMethod);
    paypalPayment.addEventListener('change', togglePaymentMethod);
    
    // Format card expiry date
    const cardExpiry = document.getElementById('card_expiry');
    cardExpiry.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });
    
    // Format card number with spaces
    const cardNumber = document.getElementById('card_number');
    cardNumber.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 16);
    });
    
    // Format CVC
    const cardCvc = document.getElementById('card_cvc');
    cardCvc.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
    });
});
</script>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
