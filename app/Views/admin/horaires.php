<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="admin-container">
    <h1>Gestion des horaires d'ouverture</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>Jour</th>
                <th>Ouverture</th>
                <th>Fermeture</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $jours = [
                1 => 'Lundi',
                2 => 'Mardi',
                3 => 'Mercredi',
                4 => 'Jeudi',
                5 => 'Vendredi',
                6 => 'Samedi',
                7 => 'Dimanche'
            ];
            foreach ($horaires as $horaire): ?>
                <tr>
                    <td><?= $jours[$horaire['jour_semaine']] ?></td>
                    <td><?= $horaire['heure_ouverture'] ?></td>
                    <td><?= $horaire['heure_fermeture'] ?></td>
                    <td>
                        <button class="btn-primary" onclick="editHoraire(<?= $horaire['id'] ?>, <?= $horaire['jour_semaine'] ?>, '<?= $horaire['heure_ouverture'] ?>', '<?= $horaire['heure_fermeture'] ?>')">
                            Modifier
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Modifier les horaires</h2>
            <form method="POST" action="<?= BASE_URL ?>/?page=admin&action=manageHoraires">
                <input type="hidden" name="jour_semaine" id="jour_semaine">
                
                <div class="form-group">
                    <label>Heure d'ouverture</label>
                    <input type="time" name="heure_ouverture" id="heure_ouverture" required>
                </div>
                
                <div class="form-group">
                    <label>Heure de fermeture</label>
                    <input type="time" name="heure_fermeture" id="heure_fermeture" required>
                </div>
                
                <button type="submit" class="btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>
    
    <script>
        function editHoraire(id, jour, ouverture, fermeture) {
            document.getElementById('jour_semaine').value = jour;
            document.getElementById('heure_ouverture').value = ouverture;
            document.getElementById('heure_fermeture').value = fermeture;
            document.getElementById('edit-modal').style.display = 'block';
        }
        
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('edit-modal').style.display = 'none';
        });
    </script>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>
