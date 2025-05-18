<?php require_once 'app/Views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Places disponibles</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="parking-container">
        <h1>Places de parking disponibles</h1>
        
        <div class="filters">
            <h3>Filtrer par type :</h3>
            <div class="filter-buttons">
                <button class="filter-btn active" data-type="all">Tous</button>
                <?php foreach($types as $type): ?>
                    <button class="filter-btn" data-type="<?= $type ?>"><?= ucfirst($type) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="parking-grid">
            <?php foreach($places as $place): ?>
                <div class="parking-spot" data-type="<?= $place['type'] ?>">
                    <h3>Place <?= htmlspecialchars($place['numero']) ?></h3>
                    <p>Type: <?= ucfirst(htmlspecialchars($place['type'])) ?></p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_URL ?>/?page=parking&action=view&id=<?= $place['id'] ?>" 
                           class="btn-primary">Réserver</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/?page=login" class="btn-secondary">
                            Connectez-vous pour réserver
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                const type = button.dataset.type;
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                document.querySelectorAll('.parking-spot').forEach(spot => {
                    if (type === 'all' || spot.dataset.type === type) {
                        spot.style.display = 'block';
                    } else {
                        spot.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php require_once 'app/Views/layouts/footer.php'; ?>
