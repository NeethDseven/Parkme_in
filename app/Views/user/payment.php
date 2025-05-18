<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement de la réservation</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Paiement de votre réservation</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="reservation-details">
            <h2>Détails de la réservation</h2>
            <p><strong>Place n°:</strong> <?= htmlspecialchars($paiement['numero']) ?></p>
            <p><strong>Du:</strong> <?= date('d/m/Y H:i', strtotime($paiement['date_debut'])) ?></p>
            <p><strong>Au:</strong> <?= date('d/m/Y H:i', strtotime($paiement['date_fin'])) ?></p>
            <p><strong>Montant:</strong> <?= number_format($paiement['montant'], 2) ?> €</p>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/?page=user&action=processPayment" class="payment-form">
            <input type="hidden" name="paiement_id" value="<?= $paiement['id'] ?>">
            
            <div class="form-group">
                <label>Numéro de carte</label>
                <input type="text" name="card_number" pattern="[0-9]{16}" required placeholder="1234 5678 9012 3456">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Date d'expiration</label>
                    <input type="text" name="card_expiry" pattern="[0-9]{2}/[0-9]{2}" required placeholder="MM/YY">
                </div>
                
                <div class="form-group">
                    <label>CVV</label>
                    <input type="text" name="card_cvv" pattern="[0-9]{3}" required placeholder="123">
                </div>
            </div>

            <button type="submit" class="btn-primary">Payer <?= number_format($paiement['montant'], 2) ?> €</button>
        </form>
    </div>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
