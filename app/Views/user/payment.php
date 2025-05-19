<?php $pageTitle = 'Paiement - Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">Paiement de votre réservation</h1>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Informations de paiement</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/?page=user&action=processPayment" class="needs-validation" novalidate>
                        <input type="hidden" name="paiement_id" value="<?= $paiement['id'] ?>">
                        
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Numéro de carte</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                <input type="text" class="form-control" id="card_number" name="card_number" 
                                       pattern="[0-9]{16}" required placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="form-text">Saisissez les 16 chiffres sans espace</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="card_expiry" class="form-label">Date d'expiration</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="text" class="form-control" id="card_expiry" name="card_expiry" 
                                           pattern="[0-9]{2}/[0-9]{2}" required placeholder="MM/YY">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="card_cvv" class="form-label">CVV</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="text" class="form-control" id="card_cvv" name="card_cvv" 
                                           pattern="[0-9]{3}" required placeholder="123">
                                </div>
                                <div class="form-text">Les 3 chiffres au dos de votre carte</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-check-circle me-2"></i>
                            Payer <?= number_format($paiement['montant'], 2) ?> €
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Résumé de la réservation</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Place n°:</span>
                            <span class="fw-bold"><?= htmlspecialchars($paiement['numero']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Du:</span>
                            <span class="fw-bold"><?= date('d/m/Y H:i', strtotime($paiement['date_debut'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Au:</span>
                            <span class="fw-bold"><?= date('d/m/Y H:i', strtotime($paiement['date_fin'])) ?></span>
                        </li>
                    </ul>
                    
                    <div class="alert alert-primary d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            Paiement 100% sécurisé avec cryptage SSL
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-5 text-primary"><?= number_format($paiement['montant'], 2) ?> €</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>
