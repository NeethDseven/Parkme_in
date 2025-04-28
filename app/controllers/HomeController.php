<?php

class HomeController extends BaseController {
    
    /**
     * Display the homepage
     */
    public function index() {
        // Check if user is logged in
        session_start();
        $isLoggedIn = isset($_SESSION['user_id']);
        
        // Include necessary models
        require_once __DIR__ . '/../models/Parking.php';
        
        // Get featured parkings to display on homepage
        $featuredParkings = Parking::getFeatured();
        
        // Render the homepage view
        $this->render('home/index', [
            'isLoggedIn' => $isLoggedIn,
            'featuredParkings' => $featuredParkings ?? []
        ]);
    }
}
?>
