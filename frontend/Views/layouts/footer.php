</div><!-- Fin du container main-content -->
    
    <!-- Pied de page -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Parkme In</h5>
                    <p>
                        <i class="fas fa-map-marker-alt me-2"></i>123 Rue du Parking<br>
                        75000 Paris, France<br>
                        <i class="fas fa-phone me-2"></i>01 23 45 67 89<br>
                        <i class="fas fa-envelope me-2"></i>contact@parkmein.com
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>/" class="text-white"><i class="fas fa-home me-2"></i>Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>/?page=parking&action=list" class="text-white"><i class="fas fa-parking me-2"></i>Places disponibles</a></li>
                        <li><a href="<?= BASE_URL ?>/#contact" class="text-white"><i class="fas fa-envelope me-2"></i>Contact</a></li>
                        <li><a href="<?= BASE_URL ?>/#about" class="text-white"><i class="fas fa-info-circle me-2"></i>À propos</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Horaires d'ouverture</h5>
                    <p>
                        Lundi - Vendredi: 8h - 20h<br>
                        Samedi: 9h - 22h<br>
                        Dimanche: 9h - 20h
                    </p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> Parkme In. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white me-3">Mentions légales</a>
                    <a href="#" class="text-white me-3">Politique de confidentialité</a>
                    <a href="#" class="text-white">CGU</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts qui doivent être chargés à la fin -->
    <script>
        // Données pour JavaScript
        <?php if (isset($jsData) && is_array($jsData)): ?>
        var <?= key_exists('hasCharts', $jsData) && $jsData['hasCharts'] ? 'hasCharts = true' : 'hasCharts = false' ?>;
            
        <?php if (key_exists('occupationData', $jsData)): ?>
        var occupationData = <?= json_encode($jsData['occupationData']) ?>;
        <?php endif; ?>
            
        <?php if (key_exists('revenueData', $jsData)): ?>
        var revenueData = <?= json_encode($jsData['revenueData']) ?>;
        <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>
