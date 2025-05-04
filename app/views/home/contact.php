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
                    <h1 class="h3 mb-0">Contactez-nous</h1>
                </div>
                <div class="card-body">
                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="h4 accent-border-left">Nous contacter</h2>
                            <p>
                                Vous avez une question ou un commentaire ? N'hésitez pas à remplir le formulaire 
                                ci-contre et nous vous répondrons dans les plus brefs délais.
                            </p>
                            
                            <div class="mt-4">
                                <h3 class="h5"><i class="bi bi-geo-alt text-primary me-2"></i>Adresse</h3>
                                <p class="ms-4">
                                    123 Avenue du Parking<br>
                                    75000 Paris, France
                                </p>
                                
                                <h3 class="h5"><i class="bi bi-telephone text-primary me-2"></i>Téléphone</h3>
                                <p class="ms-4">+33 (0)1 23 45 67 89</p>
                                
                                <h3 class="h5"><i class="bi bi-envelope text-primary me-2"></i>Email</h3>
                                <p class="ms-4">contact@parkme-in.com</p>
                                
                                <h3 class="h5"><i class="bi bi-clock text-primary me-2"></i>Horaires</h3>
                                <p class="ms-4">
                                    Lundi - Vendredi : 9h - 18h<br>
                                    Samedi : 10h - 16h<br>
                                    Fermé le dimanche
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <form action="index.php?controller=home&action=contact" method="POST">
                                <div class="form-group mb-3">
                                    <label for="name">Nom complet</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="subject">Sujet</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="message">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Envoyer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h2 class="h4 accent-border-left">Localisation</h2>
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d83998.76457410133!2d2.2769957472054055!3d48.85894658148287!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e1f06e2b70f%3A0x40b82c3688c9460!2sParis!5e0!3m2!1sfr!2sfr!4v1634580443807!5m2!1sfr!2sfr" 
                                    width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once BASE_PATH . '/app/views/includes/footer.php'; ?>
