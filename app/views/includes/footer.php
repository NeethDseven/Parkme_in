</main>
    
    <footer class="mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© <?= date('Y') ?> ParkMeIn - Tous droits réservés</span>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Notification System -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <script src="/projet/Parkme_in-master/public/js/notifications.js"></script>
    <?php endif; ?>
</body>
</html>
