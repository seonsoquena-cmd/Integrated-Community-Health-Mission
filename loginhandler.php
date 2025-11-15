<?php
// Start or resume session (necessary for storing login state)
session_start();

// Include the database configuration
require_once 'connection.php';

// Define redirection paths for each role (you will create these files next!)
$dashboard_paths = [
    'Admin' => 'admindashboard.php',
    'Health Worker' => 'hwdashboard.php',
    'Patient' => 'patientdashboard.php',
    'Guest' => 'guestdashboard.php',
];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple input validation
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please enter both username and password.";
        header("location: index.html");
        exit;
    }

    // Prepare a select statement to prevent SQL injection
    $sql = "SELECT user_id, username, password_hash, role, full_name FROM users WHERE username = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("s", $param_username);
        
        // Set parameters
        $param_username = $username;
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $stmt->store_result();
            
            // Check if username exists, if yes then verify password
            if ($stmt->num_rows == 1) {
                // Bind result variables
                $stmt->bind_result($user_id, $username, $password_hash, $role, $full_name);
                
                if ($stmt->fetch()) {
                    // Password verification (using password_verify for security)
                    // NOTE: The example hashes in SQL are placeholders. Use this function with real hashes.
                    // For now, we will use a simple placeholder check since the SQL hash is just a placeholder value.
                    // Replace the line below with a real password_verify for production!
                    if (password_verify($password, $password_hash)) {
                    // if ($password == 'password') { // Temporarily using hardcoded check for testing if hash fails
                        
                        // Password is correct, store data in session
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $user_id;
                        $_SESSION["username"] = $username;
                        $_SESSION["role"] = $role;
                        $_SESSION["full_name"] = $full_name;
                        
                        // Redirect user based on role
                        if (array_key_exists($role, $dashboard_paths)) {
                            header("location: " . $dashboard_paths[$role]);
                        } else {
                            // Default redirect if role is undefined
                            header("location: welcome.php");
                        }
                        exit;
                    } else {
                        // Display an error message if password is not valid
                        $_SESSION['error'] = "The password you entered was not valid.";
                    }
                }
            } else {
                // Display an error message if username doesn't exist
                $_SESSION['error'] = "No account found with that username.";
            }
        } else {
            $_SESSION['error'] = "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }
    
    // If we reach here, there was an error, redirect back to login
    header("location: index.html");
    exit;

}

// Close connection
$conn->close();
?>