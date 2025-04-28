<?php

/**
 * Base Model class
 */
abstract class Model
{
    /**
     * Get the database connection
     *
     * @return PDO
     */
    protected static function getDB()
    {
        static $db = null;
        
        if ($db === null) {
            // Database configuration
            $host = 'localhost';
            $dbname = 'app_gestion_parking'; // Corrected database name
            $username = 'root';
            $password = '';
            
            try {
                $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", 
                             $username, $password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo $e->getMessage();
                exit;
            }
        }
        
        return $db;
    }
}
