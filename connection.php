<?php
/*
 * Database Configuration File
 * This connects to the 'ichm_db' database.
 */

// --- Database Credentials ---
define('DB_NAME', 'test');
define('DB_USER', '39J7MiwX6J8DEds.root');
define('DB_PASSWORD', 'LZXEk4htmOlqTmkRQ'); 
define('DB_HOST', 'gateway01.us-east-1.prod.aws.tidbcloud.com');
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

