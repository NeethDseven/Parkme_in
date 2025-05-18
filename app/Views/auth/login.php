<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="auth-form">
        <h2>Connexion</h2>
        <form method="POST">
            <div>
                <label>Email :</label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label>Mot de passe :</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        <p>Pas encore inscrit ? <a href="<?= BASE_URL ?>/?page=register">S'inscrire</a></p>
    </div>
</body>
</html>
