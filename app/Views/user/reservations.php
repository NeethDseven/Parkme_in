<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes réservations</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="user-container">
        <h1>Mes réservations</h1>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('active')">Réservations actives</button>
            <button class="tab-btn" onclick="showTab('history')">Historique</button>
        </div>

        <?php if (empty($reservations)): ?>
            <p>Vous n'avez aucune réservation.</p>
        <?php else: ?>
            <div id="active-tab" class="tab-content active">
                <h2>Réservations actives</h2>
                <?php
                $hasActive = false;
                foreach($reservations as $reservation) {
                    if ($reservation['status'] === 'confirmée') {
                        $hasActive = true;
                        break;
                    }
                }
                ?>
                
                <?php if (!$hasActive): ?>
                    <p>Vous n'avez aucune réservation active.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Place</th>
                                <th>Type</th>
                                <th>Date début</th>
                                <th>Date fin</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reservations as $reservation): ?>
                                <?php if ($reservation['status'] === 'confirmée'): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($reservation['place_numero']) ?></td>
                                        <td><?= htmlspecialchars($reservation['place_type']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></td>
                                        <td><?= htmlspecialchars($reservation['status']) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/?page=user&action=cancelReservation&id=<?= $reservation['id'] ?>"
                                               onclick="return confirm('Voulez-vous annuler cette réservation ?')"
                                               class="btn-danger">Annuler</a>
                                            <a href="<?= BASE_URL ?>/?page=user&action=downloadReceipt&id=<?= $reservation['id'] ?>"
                                               class="btn-primary">PDF</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div id="history-tab" class="tab-content">
                <h2>Historique des réservations</h2>
                <?php
                $hasHistory = false;
                foreach($reservations as $reservation) {
                    if ($reservation['status'] === 'annulée') {
                        $hasHistory = true;
                        break;
                    }
                }
                ?>
                
                <?php if (!$hasHistory): ?>
                    <p>Votre historique est vide.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Place</th>
                                <th>Type</th>
                                <th>Date début</th>
                                <th>Date fin</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reservations as $reservation): ?>
                                <?php if ($reservation['status'] === 'annulée'): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($reservation['place_numero']) ?></td>
                                        <td><?= htmlspecialchars($reservation['place_type']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($reservation['date_debut'])) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($reservation['date_fin'])) ?></td>
                                        <td><?= htmlspecialchars($reservation['status']) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn-primary">Réserver une place</a>
    </div>

    <script>
    function showTab(tabName) {
        // Masquer tous les contenus des onglets
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Désactiver tous les boutons d'onglets
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Activer l'onglet sélectionné
        document.getElementById(tabName + '-tab').classList.add('active');
        
        // Activer le bouton correspondant
        document.querySelector('.tab-btn[onclick="showTab(\'' + tabName + '\')"]').classList.add('active');
    }
    </script>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
