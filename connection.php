<?php
/*
 * Database Configuration File
 * This connects to the 'ichm_db' database.
 */

// --- Database Credentials ---
define('DB_NAME', 'if0_40427412_ichm_db');     // New Database Name
define('DB_USER', 'if0_40427412');            // New Username
define('DB_PASSWORD', 'YOUR_HOSTING_PASSWORD'); // **Replace this with your InfinityFree password**
define('DB_HOST', 'sql201.infinityfree.com');  // New Hostname
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
