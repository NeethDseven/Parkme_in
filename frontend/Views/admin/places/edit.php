<?php $pageTitle = 'Modifier une place - Administration Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h1 class="h3 mb-0">Modifier la place n°<?= htmlspecialchars($place['numero']) ?></h1>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= BASE_URL ?>/?page=admin&action=editPlace&id=<?= $place['id'] ?>" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="numero" class="form-label">Numéro de la place</label>
                            <input type="text" class="form-control" id="numero" name="numero" value="<?= htmlspecialchars($place['numero']) ?>" required>
                            <div class="invalid-feedback">
                                Veuillez saisir un numéro de place.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Type de place</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="standard" <?= $place['type'] === 'standard' ? 'selected' : '' ?>>Standard</option>
                                <option value="handicape" <?= $place['type'] === 'handicape' ? 'selected' : '' ?>>PMR</option>
                                <option value="electrique" <?= $place['type'] === 'electrique' ? 'selected' : '' ?>>Électrique</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="libre" <?= $place['status'] === 'libre' ? 'selected' : '' ?>>Libre</option>
                                <option value="occupe" <?= $place['status'] === 'occupe' ? 'selected' : '' ?>>Occupée</option>
                                <option value="maintenance" <?= $place['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Retour à la liste
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
