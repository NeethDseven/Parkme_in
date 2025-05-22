<?php $pageTitle = 'Mes notifications - Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mes notifications</h1>
        
        <?php if($unreadCount > 0): ?>
        <button id="mark-all-read" class="btn btn-outline-primary">
            <i class="fas fa-check-double me-2"></i> Tout marquer comme lu
        </button>
        <?php endif; ?>
    </div>
    
    <?php if(empty($notifications)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Vous n'avez aucune notification.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="list-group list-group-flush notification-list" id="notification-list">
                <?php foreach($notifications as $notification): ?>
                    <div class="list-group-item notification-item <?= $notification['lu'] ? '' : 'unread' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-1">
                                <?php if(!$notification['lu']): ?>
                                    <span class="badge bg-primary me-2">Nouveau</span>
                                <?php endif; ?>
                                <?= htmlspecialchars($notification['titre']) ?>
                            </h5>
                            <small class="text-muted notification-date">
                                <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                            </small>
                        </div>
                        <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                        <?php if(!$notification['lu']): ?>
                            <div class="notification-actions mt-2">
                                <button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="<?= $notification['id'] ?>">
                                    <i class="fas fa-check me-1"></i> Marquer comme lu
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gérer le clic sur "Tout marquer comme lu"
    const markAllReadBtn = document.getElementById('mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Empêcher le comportement de navigation par défaut
            
            // Appel AJAX pour marquer toutes les notifications comme lues
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '<?= BASE_URL ?>/?page=user&action=mark_all_read', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Mettre à jour l'interface utilisateur
                            document.querySelectorAll('.notification-item.unread').forEach(item => {
                                item.classList.remove('unread');
                                const actionBtn = item.querySelector('.notification-actions');
                                if (actionBtn) actionBtn.remove();
                                const badge = item.querySelector('.badge.bg-primary');
                                if (badge) badge.remove();
                            });
                            
                            // Masquer le bouton "Tout marquer comme lu"
                            markAllReadBtn.style.display = 'none';
                            
                            // Feedback utilisateur
                            const alertBox = document.createElement('div');
                            alertBox.className = 'alert alert-success mt-3';
                            alertBox.innerHTML = '<i class="fas fa-check-circle me-2"></i> Toutes les notifications ont été marquées comme lues';
                            document.querySelector('.container').prepend(alertBox);
                            
                            // Faire disparaître l'alerte après 3 secondes
                            setTimeout(() => {
                                alertBox.style.opacity = '0';
                                alertBox.style.transition = 'opacity 0.5s';
                                setTimeout(() => alertBox.remove(), 500);
                            }, 3000);
                        }
                    } catch (e) {
                        console.error('Erreur lors du parsing de la réponse:', e);
                    }
                }
            };
            
            xhr.send();
        });
    }
    
    // Gérer le clic sur les boutons "Marquer comme lu" individuels
    document.querySelectorAll('.mark-read-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.dataset.id;
            const notificationItem = this.closest('.notification-item');
            
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `<?= BASE_URL ?>/?page=user&action=markNotificationRead&id=${notificationId}`, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Mettre à jour l'interface utilisateur
                            notificationItem.classList.remove('unread');
                            const actionBtn = notificationItem.querySelector('.notification-actions');
                            if (actionBtn) actionBtn.remove();
                            
                            // Retirer le badge "Nouveau"
                            const badge = notificationItem.querySelector('.badge.bg-primary');
                            if (badge) badge.remove();
                            
                            // Si plus aucune notification non lue, masquer le bouton "Tout marquer comme lu"
                            if (response.unread_count === 0 && markAllReadBtn) {
                                markAllReadBtn.style.display = 'none';
                            }
                        }
                    } catch (e) {
                        console.error('Erreur lors du parsing de la réponse:', e);
                    }
                }
            };
            
            xhr.send();
        });
    });
});
</script>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
