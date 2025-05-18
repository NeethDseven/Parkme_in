<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container">
    <h1>Mon profil</h1>
    
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
    
    <form method="POST" action="<?= BASE_URL ?>/?page=user&action=updateProfile">
        <div class="profile-form">
            <div class="form-section">
                <h2>Informations personnelles</h2>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    <small>L'email ne peut pas être modifié</small>
                </div>
                
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>">
                </div>
            </div>
            
            <div class="form-section">
                <h2>Préférences</h2>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="notifications_active" name="notifications_active" <?= $user['notifications_active'] ? 'checked' : '' ?>>
                    <label for="notifications_active">Recevoir des notifications</label>
                </div>
                
                <h3>Préférences de paiement</h3>
                
                <div class="form-group">
                    <label for="default_payment">Méthode de paiement par défaut</label>
                    <select id="default_payment" name="default_payment">
                        <option value="carte" <?= ($paymentPreferences['default_method'] ?? '') === 'carte' ? 'selected' : '' ?>>Carte bancaire</option>
                        <option value="paypal" <?= ($paymentPreferences['default_method'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                    </select>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="save_card_info" name="save_card_info" <?= ($paymentPreferences['save_card_info'] ?? false) ? 'checked' : '' ?>>
                    <label for="save_card_info">Enregistrer mes informations de carte</label>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn-primary">Enregistrer les modifications</button>
    </form>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>
