<?php $pageTitle = 'Ajouter une place - Administration Parkme In'; ?>
<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Ajouter une place</h1>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="numero" class="form-label">Numéro de place</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                <input type="text" class="form-control" id="numero" name="numero" required>
                            </div>
                            <div class="invalid-feedback">Veuillez saisir un numéro de place.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="standard">Standard</option>
                                <option value="handicape">Handicapé</option>
                                <option value="electrique">Véhicule électrique</option>
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner un type de place.</div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i> Ajouter
                            </button>
                            <a href="<?= BASE_URL ?>/?page=admin&action=places" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>
