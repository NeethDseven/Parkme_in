<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <div class="auth-form">
        <h2>Inscription</h2>
        <form method="POST">
            <div>
                <label>Nom :</label>
                <input type="text" name="nom" required>
            </div>
            <div>
                <label>Prénom :</label>
                <input type="text" name="prenom" required>
            </div>
            <div>
                <label>Email :</label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label>Mot de passe :</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">S'inscrire</button>
        </form>
        <p>Déjà inscrit ? <a href="<?= BASE_URL ?>/?page=login">Se connecter</a></p>
    </div>
</body>
</html>
