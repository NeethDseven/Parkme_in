<?php
// Define the base path for includes and assets if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
}

include_once BASE_PATH . '/app/views/includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow fade-in">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0">À propos de ParkMeIn</h1>
                </div>
                <div class="card-body">
                    <h2 class="h4 accent-border-left">Notre mission</h2>
                    <p class="mb-4">
                        Chez ParkMeIn, notre mission est de simplifier le stationnement urbain en offrant une solution 
                        efficace et intuitive pour la recherche, la réservation et la gestion des places de parking. 
                        Nous croyons qu'en rendant le stationnement plus accessible et organisé, nous pouvons contribuer 
                        à réduire le stress des conducteurs et à fluidifier la circulation dans les zones urbaines.
                    </p>
                    
                    <h2 class="h4 accent-border-left">Notre histoire</h2>
                    <p class="mb-4">
                        ParkMeIn a été fondé en 2023 avec l'idée simple que le stationnement ne devrait pas être une source
                        de stress. Après avoir constaté les difficultés rencontrées quotidiennement par les conducteurs en 
                        recherche de stationnement, notre équipe a décidé de créer une plateforme centralisée pour connecter 
                        les propriétaires de parkings avec les conducteurs en quête d'une place.
                    </p>
                    
                    <h2 class="h4 accent-border-left">Notre équipe</h2>
                    <p class="mb-4">
                        Notre équipe est composée de professionnels passionnés par l'innovation et l'amélioration des 
                        services urbains. Nous combinons des expertises en développement logiciel, en expérience utilisateur 
                        et en gestion de services pour offrir une solution complète et fiable.
                    </p>
                    
                    <h2 class="h4 accent-border-left">Nos valeurs</h2>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card mb-3 h-100 bg-light-custom border-0">
                                <div class="card-body">
                                    <h3 class="h5 mb-3"><i class="bi bi-shield-check text-primary me-2"></i> Fiabilité</h3>
                                    <p class="mb-0 small">Nous nous engageons à fournir un service fiable sur lequel nos utilisateurs peuvent compter.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3 h-100 bg-light-custom border-0">
                                <div class="card-body">
                                    <h3 class="h5 mb-3"><i class="bi bi-lightning text-primary me-2"></i> Simplicité</h3>
                                    <p class="mb-0 small">Nous croyons en la simplicité et nous nous efforçons de rendre notre service intuitif et facile à utiliser.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3 h-100 bg-light-custom border-0">
                                <div class="card-body">
                                    <h3 class="h5 mb-3"><i class="bi bi-people text-primary me-2"></i> Communauté</h3>
                                    <p class="mb-0 small">Nous valorisons la communauté et nous nous efforçons de créer un service qui profite à tous.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3 h-100 bg-light-custom border-0">
                                <div class="card-body">
                                    <h3 class="h5 mb-3"><i class="bi bi-tree text-primary me-2"></i> Durabilité</h3>
                                    <p class="mb-0 small">Nous nous engageons à développer des solutions qui contribuent à un environnement urbain plus durable.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h2 class="h4 accent-border-left">Contact</h2>
                    <p class="mb-0">
                        Si vous avez des questions ou des suggestions, n'hésitez pas à nous contacter via notre 
                        <a href="index.php?controller=home&action=contact">formulaire de contact</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
