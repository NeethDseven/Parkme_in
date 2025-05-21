</div><!-- Fin du container main-content -->
    
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Parkme In</h5>
                    <p>Une solution simple et efficace pour gérer votre stationnement.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liens utiles</h5>
                    <ul class="list-unstyled">
                        <li><a href="/Projet/Parking%20final/?page=home" class="text-light">Accueil</a></li>
                        <li><a href="/Projet/Parking%20final/?page=parking&action=list" class="text-light">Places disponibles</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="/Projet/Parking%20final/?page=user&action=reservations" class="text-light">Mes réservations</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <address class="text-light">
                        <strong>Parkme In</strong><br>
                        123 Avenue des Places<br>
                        75000 Paris<br>
                        <i class="fas fa-phone me-2"></i> 01.23.45.67.89<br>
                        <i class="fas fa-envelope me-2"></i> <a href="mailto:contact@parking-app.com" class="text-light">contact@parking-app.com</a>
                    </address>
                </div>
            </div>
            <div class="text-center mt-4 border-top pt-3">
                <p class="mb-0">&copy; <?= date('Y') ?> Parkme In. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS avec chemin direct -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

    <!-- jQuery (pour les fonctionnalités avancées) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!-- Script personnalisé avec chemin absolu -->
    <script src="/Projet/Parking%20final/public/js/app.js"></script>

    <?php if(isset($extraJS)): ?>
        <?php foreach($extraJS as $js): ?>
            <script src="/Projet/Parking%20final/public/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Debug: Vérifier le chargement de Bootstrap -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Bootstrap disponible:", typeof bootstrap !== 'undefined');
        });
    </script>
</body>
</html>
