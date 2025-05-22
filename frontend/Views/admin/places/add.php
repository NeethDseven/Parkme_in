<?php $pageTitle = 'Ajouter une place - Administration Parkme In'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Ajouter une place de parking</h1>
            <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Retour à la liste
            </a>
        </div>
        <div class="card-body p-4">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form method="POST" action="<?= BASE_URL ?>/?page=admin&action=addPlace" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="numero" class="form-label">Numéro de place <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="numero" name="numero" value="<?= isset($_POST['numero']) ? htmlspecialchars($_POST['numero']) : '' ?>" required>
                    <div class="invalid-feedback">
                        Veuillez saisir un numéro de place.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="type" class="form-label">Type de place</label>
                    <select class="form-select" id="type" name="type">
                        <option value="standard" <?= (isset($_POST['type']) && $_POST['type'] === 'standard') ? 'selected' : '' ?>>Standard</option>
                        <option value="handicape" <?= (isset($_POST['type']) && $_POST['type'] === 'handicape') ? 'selected' : '' ?>>PMR</option>
                        <option value="electrique" <?= (isset($_POST['type']) && $_POST['type'] === 'electrique') ? 'selected' : '' ?>>Borne électrique</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" id="status" name="status">
                        <option value="libre" <?= (isset($_POST['status']) && $_POST['status'] === 'libre') ? 'selected' : '' ?>>Libre</option>
                        <option value="occupe" <?= (isset($_POST['status']) && $_POST['status'] === 'occupe') ? 'selected' : '' ?>>Occupée</option>
                        <option value="maintenance" <?= (isset($_POST['status']) && $_POST['status'] === 'maintenance') ? 'selected' : '' ?>>En maintenance</option>
                    </select>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> Ajouter la place
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
