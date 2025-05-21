<?php require_once 'frontend/Views/layouts/header.php'; ?>

<?php $pageTitle = 'Accueil - Parkme In'; ?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-8">
            <div class="mb-4">
                <h1 class="display-4 fw-bold">Bienvenue sur Parkme In</h1>
                <p class="lead text-secondary">La solution simple pour trouver et réserver votre place de parking.</p>
            </div>
            
            <div class="d-grid gap-2 d-md-flex mt-4">
                <a href="<?php echo BASE_URL; ?>/?page=parking&action=list" class="btn btn-primary btn-lg">
                    <i class="fas fa-search me-2"></i> Trouver une place
                </a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_URL; ?>/?page=register" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-user-plus me-2"></i> Créer un compte
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-center justify-content-center">
            <div class="card bg-light text-center p-4 w-100 shadow-sm">
                <h3 class="mb-3">Places disponibles</h3>
                <?php
                // Récupérer les places disponibles depuis le contrôleur
                $placesDisponibles = $stats['places_libres'];
                $placesTotales = $stats['places_totales'];
                $pourcentage = ($placesDisponibles / $placesTotales) * 100;
                
                // Déterminer la couleur en fonction du pourcentage
                $couleur = 'success';
                if ($pourcentage < 30) {
                    $couleur = 'danger';
                } elseif ($pourcentage < 70) {
                    $couleur = 'warning';
                }
                ?>
                <div class="display-1 fw-bold text-<?= $couleur ?> mb-2">
                    <?= $placesDisponibles ?> / <?= $placesTotales ?>
                </div>
                <p class="text-muted">Places actuellement disponibles</p>
                <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar bg-<?= $couleur ?>" role="progressbar" 
                         style="width: <?= $pourcentage ?>%" 
                         aria-valuenow="<?= $placesDisponibles ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="<?= $placesTotales ?>"></div>
                </div>
                <a href="<?= BASE_URL ?>/?page=parking&action=list" class="btn btn-<?= $couleur ?>">
                    <i class="fas fa-parking me-2"></i> Voir les places
                </a>
            </div>
        </div>
    </div>

    <div class="row my-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">Nos services</h2>
            <p class="text-muted">Découvrez comment nous pouvons vous faciliter la vie</p>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-search fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Recherche facile</h5>
                    <p class="card-text">Trouvez rapidement une place de parking disponible près de votre destination.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-calendar-check fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Réservation en ligne</h5>
                    <p class="card-text">Réservez votre place à l'avance et assurez-vous d'avoir un emplacement garanti.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">Paiement sécurisé</h5>
                    <p class="card-text">Payez en toute sécurité avec notre système de paiement en ligne intégré.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5 py-4 bg-light rounded shadow-sm">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">Comment ça marche ?</h2>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-user-plus fa-lg"></i>
                    </div>
                    <h5 class="card-title">Créez un compte</h5>
                    <p class="card-text small">Inscrivez-vous rapidement et gratuitement.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-search fa-lg"></i>
                    </div>
                    <h5 class="card-title">Trouvez une place</h5>
                    <p class="card-text small">Consultez les places disponibles.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-calendar-alt fa-lg"></i>
                    </div>
                    <h5 class="card-title">Réservez</h5>
                    <p class="card-text small">Choisissez vos dates et heures.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-credit-card fa-lg"></i>
                    </div>
                    <h5 class="card-title">Payez</h5>
                    <p class="card-text small">Effectuez votre paiement en ligne.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ajout d'une section pour montrer les types de places disponibles -->
    <div class="row mt-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">Nos types de places</h2>
            <p class="text-muted">Nous proposons différentes solutions adaptées à vos besoins</p>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Standard</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Places de taille standard</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Accessibles 24h/24</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Surveillance vidéo</li>
                    </ul>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">À partir de 2€/heure</span>
                        <a href="<?= BASE_URL ?>/?page=parking&action=list&type=standard" class="btn btn-sm btn-outline-primary">Voir</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">PMR</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Places plus larges</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Proximité des accès</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Tarif préférentiel</li>
                    </ul>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">À partir de 1,50€/heure</span>
                        <a href="<?= BASE_URL ?>/?page=parking&action=list&type=handicape" class="btn btn-sm btn-outline-info">Voir</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Électrique</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Bornes de recharge</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Puissance jusqu'à 22kW</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Compatible toutes marques</li>
                    </ul>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">À partir de 3€/heure</span>
                        <a href="<?= BASE_URL ?>/?page=parking&action=list&type=electrique" class="btn btn-sm btn-outline-success">Voir</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'frontend/Views/layouts/footer.php'; ?>
