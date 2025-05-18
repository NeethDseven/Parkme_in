<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des remboursements</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Gestion des remboursements</h1>
        
        <?php if (empty($remboursements)): ?>
            <p>Aucune demande de remboursement.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date demande</th>
                        <th>Client</th>
                        <th>Réservation</th>
                        <th>Montant</th>
                        <th>Raison</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($remboursements as $remb): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($remb['date_demande'])) ?></td>
                        <td><?= htmlspecialchars($remb['nom']) ?> <?= htmlspecialchars($remb['prenom']) ?></td>
                        <td>
                            Place <?= isset($remb['place_numero']) ? htmlspecialchars($remb['place_numero']) : 'N/A' ?><br>
                            <?= isset($remb['date_debut']) ? date('d/m/Y', strtotime($remb['date_debut'])) : 'N/A' ?>
                        </td>
                        <td><?= number_format($remb['montant'], 2) ?> €</td>
                        <td><?= htmlspecialchars($remb['raison']) ?></td>
                        <td><?= htmlspecialchars($remb['status']) ?></td>
                        <td>
                            <?php if($remb['status'] === 'en_cours'): ?>
                                <form method="POST" action="<?= BASE_URL ?>/?page=admin&action=processRefund">
                                    <input type="hidden" name="remboursement_id" value="<?= $remb['id'] ?>">
                                    <button type="submit" name="decision" value="accepte" class="btn-success">Accepter</button>
                                    <button type="submit" name="decision" value="refuse" class="btn-danger">Refuser</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
