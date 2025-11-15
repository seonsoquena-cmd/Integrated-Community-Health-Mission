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


/* * Attempt to connect to MySQL database securely using SSL */
// 1. Initialize MySQLi
$conn = mysqli_init();

// 2. Set SSL parameters, pointing to the certificate file we uploaded
// The path /var/www/html/ is where your tidb_ca.pem file is located in the Render server
mysqli_ssl_set($conn, NULL, NULL, '/var/www/html/tidb_ca.pem', NULL, NULL);

// 3. Connect securely using mysqli_real_connect with port 4000 and the SSL flag
// This function replaces 'new mysqli()' for secure connections.
if (!mysqli_real_connect($conn, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 4000, NULL, MYSQLI_CLIENT_SSL)) {
    // If connection fails, show the specific error
    die("CRITICAL ERROR: Could not connect to database TiDB. Reason: " . mysqli_connect_error());
}

// Set the character set (This fixes the fatal error)
$conn->set_charset("utf8mb4");

?>
