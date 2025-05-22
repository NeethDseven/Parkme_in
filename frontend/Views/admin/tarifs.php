<?php $pageTitle = 'Gestion des tarifs - Administration'; ?>
<?php require_once 'frontend/Views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des tarifs</h1>
        <a href="<?= BASE_URL ?>/?page=admin" class="btn btn-primary">
            <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
        </a>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Modifier les tarifs</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/?page=admin&action=tarifs" method="post" class="needs-validation" novalidate>
                <!-- Places standard -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        Places standard
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="prix_heure_standard" class="form-label">Prix par heure</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix_heure_standard" name="prix_heure_standard" 
                                           value="<?= $tarifsByType['standard']['prix_heure'] ?? 2.00 ?>" min="0" step="0.01" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="prix_journee_standard" class="form-label">Prix par jour</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix_journee_standard" name="prix_journee_standard" 
                                           value="<?= $tarifsByType['standard']['prix_journee'] ?? 20.00 ?>" min="0" step="0.01" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="prix_mois_standard" class="form-label">Prix par mois</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix_mois_standard" name="prix_mois_standard" 
                                           value="<?= $tarifsByType['standard']['prix_mois'] ?? 200.00 ?>" min="0" step="0.01" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Places handicapés -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        Places pour personnes handicapées
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="prix_heure_handicape" class="form-label">Prix par heure</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix_heure_handicape" name="prix_heure_handicape" 
                                           value="<?= $tarifsByType['handicape']['prix_heure'] ?? 1.50 ?>" min="0" step="0.01" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="prix_journee_handicape" class="form-label">Prix par jour</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix_journee_handicape" name="prix_journee_handicape" 
                                           value="<?= $tarifsByType['handicape']['prix_journee'] ?? 15.00 ?>" min="0" step="0.01" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="prix_mois_handicape" class="form-label">Prix par mois</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix_mois_handicape" name="prix_mois_handicape" 
                                           value="<?= $tarifsByType['handicape']['prix_mois'] ?? 150.00 ?>" min="0" step="0.01" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Places électriques -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        Places avec borne électrique
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="prix_heure_electrique" class="form-label">Prix par heure</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix_heure_electrique" name="prix_heure_electrique" 
                                           value="<?= $tarifsByType['electrique']['prix_heure'] ?? 3.00 ?>" min="0" step="0.01" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="prix_journee_electrique" class="form-label">Prix par jour</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix_journee_electrique" name="prix_journee_electrique" 
                                           value="<?= $tarifsByType['electrique']['prix_journee'] ?? 25.00 ?>" min="0" step="0.01" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="prix_mois_electrique" class="form-label">Prix par mois</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="prix_mois_electrique" name="prix_mois_electrique" 
                                           value="<?= $tarifsByType['electrique']['prix_mois'] ?? 250.00 ?>" min="0" step="0.01" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
