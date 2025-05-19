<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des paiements - Parkme In</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Historique des paiements</h1>
        <table class="payment-history">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Place</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($historique as $paiement): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($paiement['date_paiement'])) ?></td>
                    <td>N°<?= htmlspecialchars($paiement['place_numero']) ?></td>
                    <td><?= number_format($paiement['montant'], 2) ?> €</td>
                    <td><?= htmlspecialchars($paiement['status']) ?></td>
                    <td>
                        <?php if($paiement['status'] === 'valide' && !isset($paiement['remboursement_status'])): ?>
                            <button onclick="showRefundForm(<?= $paiement['id'] ?>)" class="btn-secondary">
                                Demander remboursement
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="refundModal" class="modal">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/?page=user&action=refund" method="POST">
                <input type="hidden" name="paiement_id" id="paiement_id">
                <div class="form-group">
                    <label>Raison du remboursement :</label>
                    <textarea name="raison" required></textarea>
                </div>
                <button type="submit" class="btn-primary">Confirmer</button>
                <button type="button" onclick="closeRefundModal()" class="btn-secondary">Annuler</button>
            </form>
        </div>
    </div>

    <script>
        function showRefundForm(paiementId) {
            document.getElementById('paiement_id').value = paiementId;
            document.getElementById('refundModal').style.display = 'block';
        }

        function closeRefundModal() {
            document.getElementById('refundModal').style.display = 'none';
        }
    </script>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
