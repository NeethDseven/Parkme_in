<?php
/**
 * Helper functions for the application
 */

/**
 * Generate a proper URL with the base URL and path
 * 
 * @param string $path The path after the base URL
 * @return string The complete URL
 */
function url($path) {
    // Remove leading slash if present
    $path = ltrim($path, '/');
    
    // Ensure BASE_URL ends with a slash
    $baseUrl = rtrim(BASE_URL, '/') . '/';
    
    return $baseUrl . $path;
}
?>
