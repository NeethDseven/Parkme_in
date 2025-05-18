<?php require_once 'app/Views/layouts/header.php'; ?>

<h1>Mes notifications</h1>

<?php if (empty($notifications)): ?>
    <p>Vous n'avez aucune notification.</p>
<?php else: ?>
    <div class="notifications-list">
        <?php foreach($notifications as $notification): ?>
            <div class="notification-item <?= $notification['lu'] ? 'read' : 'unread' ?> notification-<?= htmlspecialchars($notification['type']) ?>">
                <div class="notification-header">
                    <h3><?= htmlspecialchars($notification['titre']) ?></h3>
                    <span class="notification-date"><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></span>
                </div>
                <div class="notification-content">
                    <?= htmlspecialchars($notification['message']) ?>
                </div>
                <?php if (!$notification['lu']): ?>
                    <a href="<?= BASE_URL ?>/?page=user&action=markNotificationRead&id=<?= $notification['id'] ?>" class="btn-secondary">
                        Marquer comme lu
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'app/Views/layouts/footer.php'; ?>
