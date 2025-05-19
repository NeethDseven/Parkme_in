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
                        <li><a href="<?= BASE_URL ?>?page=home" class="text-light">Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>?page=parking&action=list" class="text-light">Places disponibles</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="<?= BASE_URL ?>?page=user&action=reservations" class="text-light">Mes réservations</a></li>
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

    <!-- Bootstrap JS avec Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (pour les fonctionnalités avancées) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Script personnalisé -->
    <script src="<?= PUBLIC_URL ?>/js/app.js"></script>

    <?php if(isset($extraJS)): ?>
        <?php foreach($extraJS as $js): ?>
            <script src="<?= PUBLIC_URL ?>/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
