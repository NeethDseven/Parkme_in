<?php $pageTitle = 'Mon profil - Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- Carte de profil -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card me-2"></i>Mon profil
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="avatar-circle mb-3 mx-auto">
                        <i class="fas fa-user fa-4x text-primary"></i>
                    </div>
                    <h4><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h4>
                    <p class="text-muted mb-1">
                        <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user['email']) ?>
                    </p>
                    <?php if (!empty($user['telephone'])): ?>
                    <p class="text-muted">
                        <i class="fas fa-phone me-2"></i><?= htmlspecialchars($user['telephone']) ?>
                    </p>
                    <?php endif; ?>
                    <p class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'success' ?> mt-2">
                        <?= $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur' ?>
                    </p>
                    <p class="small text-muted mt-3">
                        <i class="fas fa-clock me-1"></i>Membre depuis <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Onglets pour les différentes sections du profil -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true">
                                <i class="fas fa-user me-2"></i>Informations personnelles
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab" aria-controls="preferences" aria-selected="false">
                                <i class="fas fa-cog me-2"></i>Préférences
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="false">
                                <i class="fas fa-key me-2"></i>Mot de passe
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Informations personnelles -->
                        <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                            <h5 class="card-title">Modifier mes informations</h5>
                            <form action="<?= BASE_URL ?>/?page=user&action=updateProfile" method="post" class="needs-validation" novalidate>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="prenom" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                                        <div class="invalid-feedback">
                                            Veuillez entrer votre prénom.
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nom" class="form-label">Nom</label>
                                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                                        <div class="invalid-feedback">
                                            Veuillez entrer votre nom.
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer une adresse email valide.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                                    <div class="form-text">Facultatif, mais recommandé pour les notifications</div>
                                </div>
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Préférences -->
                        <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                            <h5 class="card-title">Préférences de paiement et notifications</h5>
                            <form id="user-preferences-form" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="payment-method" class="form-label">Méthode de paiement par défaut</label>
                                    <select class="form-select" id="payment-method" name="default_payment">
                                        <option value="carte" <?= ($paymentPreferences['default_method'] ?? '') === 'carte' ? 'selected' : '' ?>>Carte bancaire</option>
                                        <option value="paypal" <?= ($paymentPreferences['default_method'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                                    </select>
                                </div>
                                
                                <div id="card-fields" class="mb-3 border-start ps-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="save-card-info" name="save_card_info" <?= ($paymentPreferences['save_card_info'] ?? false) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="save-card-info">
                                            Mémoriser les informations de ma carte
                                        </label>
                                        <div class="form-text">Pour un paiement plus rapide lors de vos prochaines réservations</div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="notifications-toggle" name="notifications_active" <?= $user['notifications_active'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="notifications-toggle">
                                            Recevoir des notifications
                                        </label>
                                    </div>
                                    <div class="form-text">Rester informé des confirmations de réservation, rappels et offres spéciales</div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" id="save-preferences" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les préférences
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Modification du mot de passe -->
                        <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                            <h5 class="card-title">Changer mon mot de passe</h5>
                            <form action="<?= BASE_URL ?>/?page=user&action=updatePassword" method="post" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="current-password" class="form-label">Mot de passe actuel</label>
                                    <input type="password" class="form-control" id="current-password" name="current_password" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer votre mot de passe actuel.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="new-password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="new-password" name="new_password" required minlength="8">
                                    <div class="form-text">Au moins 8 caractères, avec une majuscule, une minuscule et un chiffre</div>
                                    <div class="invalid-feedback">
                                        Le mot de passe doit contenir au moins 8 caractères.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm-password" class="form-label">Confirmer le nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                                    <div class="invalid-feedback">
                                        Les mots de passe ne correspondent pas.
                                    </div>
                                </div>
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key me-2"></i>Mettre à jour le mot de passe
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Préférences de notifications -->
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bell me-2"></i>Préférences de notifications
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= BASE_URL ?>/?page=user&action=updateNotificationPreferences">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notifications_active" name="notifications_active" <?= $user['notifications_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="notifications_active">Recevoir des notifications</label>
                        </div>
                        
                        <div class="mt-3 notification-preferences">
                            <h6 class="mb-3">Types de notifications :</h6>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notif_reservation_start" name="notification_preferences[reservation_start]" <?= isset($notificationPreferences['reservation_start']) && $notificationPreferences['reservation_start'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notif_reservation_start">Début de réservation</label>
                                <div class="form-text">Recevez une notification lorsque votre réservation commence</div>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notif_reservation_end" name="notification_preferences[reservation_end]" <?= isset($notificationPreferences['reservation_end']) && $notificationPreferences['reservation_end'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notif_reservation_end">Fin de réservation</label>
                                <div class="form-text">Recevez une notification lorsque votre réservation se termine</div>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notif_reminder" name="notification_preferences[reminder]" <?= isset($notificationPreferences['reminder']) && $notificationPreferences['reminder'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notif_reminder">Rappels</label>
                                <div class="form-text">Recevez des rappels 24h avant le début de votre réservation</div>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notif_payments" name="notification_preferences[payments]" <?= isset($notificationPreferences['payments']) && $notificationPreferences['payments'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notif_payments">Paiements</label>
                                <div class="form-text">Recevez des notifications concernant vos paiements</div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="notif_alerts" name="notification_preferences[alerts]" <?= isset($notificationPreferences['alerts']) && $notificationPreferences['alerts'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notif_alerts">Alertes</label>
                                <div class="form-text">Recevez des notifications lorsqu'une place se libère</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les préférences
                                </button>
                            </div>
                        </div>
                    </form
