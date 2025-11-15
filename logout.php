<?php
// This MUST be the first line
session_start();

// --- Activity Log ---
// Check if user_id is set before logging
if (isset($_SESSION['user_id'])) {
    require_once 'connection.php';
    $user_id = $_SESSION['user_id'];
    $activity_type = 'Logout';
    
    $sql = "INSERT INTO activity_log (user_id, activity_type) VALUES (?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("is", $user_id, $activity_type);
        $stmt->execute();
        $stmt->close();
    }
    $conn->close();
}
// --------------------

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
header("location: index.php");
exit;
?>