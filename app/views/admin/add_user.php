<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur - ParkMeIn</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row my-4">
            <div class="col-12">
                <div class="card shadow fade-in">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Ajouter un utilisateur</h2>
                        <div class="nav-buttons">
                            <a href="index.php?controller=admin&action=users" class="btn btn-outline-light me-2">
                                <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                            </a>
                            <a href="index.php?controller=dashboard&action=index" class="btn btn-light">
                                <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="admin-form">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="nom" name="nom" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Rôle</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <select class="form-select" id="role" name="role">
                                        <option value="user">Utilisateur</option>
                                        <option value="admin">Administrateur</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <small class="text-muted-custom">Le mot de passe doit contenir au moins 6 caractères.</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-person-plus-fill me-2"></i>Ajouter l'utilisateur
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
