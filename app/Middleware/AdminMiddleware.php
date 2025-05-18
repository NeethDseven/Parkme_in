<?php
class AdminMiddleware {
    public function check() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }
        return true;
    }
}
