<?php
/*
 * Database Configuration File
 * This connects to the 'ichm_db' database.
 */

// --- Database Credentials ---
define('DB_NAME', 'ichm_db'); 
define('DB_USER', 'root'); 
define('DB_PASSWORD', ''); 
define('DB_HOST', 'localhost'); 
// --- End of Credentials ---


/* * Attempt to connect to MySQL database */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("CRITICAL ERROR: Could not connect to database 'ichm_db'. " . $conn->connect_error);
}

// Set the character set (This fixes the fatal error)
$conn->set_charset("utf8mb4");

?>