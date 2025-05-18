<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver une place</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Réservation de la place n°<?= htmlspecialchars($place['numero']) ?></h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="place-details">
            <p><strong>Type :</strong> <?= htmlspecialchars($place['type']) ?></p>
            <p><strong>Tarif horaire :</strong> <?= number_format($place['prix_heure'], 2) ?> €/h</p>
            <p><strong>Tarif journalier :</strong> <?= number_format($place['prix_journee'], 2) ?> €/jour</p>
        </div>

        <form method="POST" class="reservation-form">
            <div class="form-group">
                <label>Date et heure de début :</label>
                <input type="datetime-local" name="date_debut" required 
                       min="<?= date('Y-m-d\TH:i') ?>" id="date_debut">
            </div>
            
            <div class="form-group">
                <label>Date et heure de fin :</label>
                <input type="datetime-local" name="date_fin" required 
                       min="<?= date('Y-m-d\TH:i') ?>" id="date_fin">
            </div>
            
            <div class="form-group">
                <label>Prix estimé :</label>
                <div id="prix_estime">-- €</div>
            </div>

            <button type="submit" class="btn-primary">Réserver</button>
            <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn-secondary">Retour</a>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateDebut = document.getElementById('date_debut');
        const dateFin = document.getElementById('date_fin');
        const prixEstime = document.getElementById('prix_estime');
        const tarifHoraire = <?= $place['prix_heure'] ?>;
        const tarifJournalier = <?= $place['prix_journee'] ?>;
        
        function calculerPrix() {
            if (!dateDebut.value || !dateFin.value) return;
            
            const debut = new Date(dateDebut.value);
            const fin = new Date(dateFin.value);
            
            if (fin <= debut) {
                prixEstime.textContent = "Date de fin invalide";
                return;
            }
            
            const dureeHeures = (fin - debut) / 1000 / 60 / 60;
            let prix;
            
            if (dureeHeures <= 24) {
                prix = dureeHeures * tarifHoraire;
            } else {
                prix = Math.ceil(dureeHeures / 24) * tarifJournalier;
            }
            
            prixEstime.textContent = prix.toFixed(2) + " €";
        }
        
        dateDebut.addEventListener('change', calculerPrix);
        dateFin.addEventListener('change', calculerPrix);
    });
    </script>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
