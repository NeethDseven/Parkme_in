</div><!-- Fin du container main-content -->
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>À propos</h3>
                    <p>Parking App est une application de gestion de parking développée avec PHP.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Liens rapides</h3>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/">Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>/?page=parking&action=list">Places disponibles</a></li>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <li><a href="<?= BASE_URL ?>/?page=login">Connexion</a></li>
                        <?php else: ?>
                            <li><a href="<?= BASE_URL ?>/?page=user&action=reservations">Mes réservations</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: contact@parkingapp.com</p>
                    <p>Téléphone: 01 23 45 67 89</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> Parking App - Tous droits réservés
            </div>
        </div>
    </footer>
</body>
</html>
