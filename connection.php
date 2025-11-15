<?php

// This MUST be the very first line

session_start();



// Clear any old error messages

unset($_SESSION['error']);

unset($_SESSION['message']);



// Include the database connection file

require_once 'connection.php';



// Define the dashboard paths

$dashboard_paths = [

    'Admin' => 'admindashboard.php',

    'Health Worker' => 'hwdashboard.php',

    'Patient' => 'patientdashboard.php',

    'Guest' => 'guestdashboard.php',

];



// Check if the form was submitted

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    

    // Use trim() to remove invisible whitespace

    $username = trim($_POST['username'] ?? '');

    $password = trim($_POST['password'] ?? '');

    

    if (empty($username) || empty($password)) {

        $_SESSION['error'] = "Please enter both username and password.";

        header("location: index.php"); 

        exit;

    }



    // *** MODIFIED SQL: Select 'password_text' instead of 'password_hash' ***

    $sql = "SELECT user_id, username, password_text, role, full_name FROM users WHERE username = ?";

    

    if ($stmt = $conn->prepare($sql)) {

        $stmt->bind_param("s", $username);

        

        if ($stmt->execute()) {

            $stmt->store_result();

            

            if ($stmt->num_rows == 1) {

                // *** MODIFIED BINDING ***

                $stmt->bind_result($user_id, $username, $password_text, $role, $full_name);

                

                if ($stmt->fetch()) {

                    

                    // --- THIS IS THE FINAL FIX ---

                    // We are now checking plain text, not a hash

                    if ($password === $password_text) {

                        

                        // --- SUCCESS! ---

                        session_regenerate_id(true); // Security step

                        $_SESSION["loggedin"] = true;

                        $_SESSION["user_id"] = $user_id; 

                        $_SESSION["username"] = $username;

                        $_SESSION["role"] = $role;

                        $_SESSION["full_name"] = $full_name;

                        

                        // --- START: ACTIVITY LOG ---

                        $activity_type = 'Login';

                        $log_sql = "INSERT INTO activity_log (user_id, activity_type) VALUES (?, ?)";

                        if ($log_stmt = $conn->prepare($log_sql)) {

                            $log_stmt->bind_param("is", $user_id, $activity_type);

                            $log_stmt->execute();

                            $log_stmt->close();

                        }

                        // --- END: ACTIVITY LOG ---

                        

                        if (array_key_exists($role, $dashboard_paths)) {

                            if (file_exists($dashboard_paths[$role])) {

                                header("location: " . $dashboard_paths[$role]);

                            } else {

                                $_SESSION['error'] = "Error: Your dashboard file '{$dashboard_paths[$role]}' is missing.";

                                header("location: index.php");

                            }

                        } else {

                            $_SESSION['error'] = "Error: No dashboard defined for your role ('$role').";

                            header("location: index.php"); 

                        }

                        exit; 

                        

                    } else {

                        $_SESSION['error'] = "Login Failed: Invalid password for user '$username'.";

                    }

                }

            } else {

                $_SESSION['error'] = "Login Failed: No account found with username '$username'.";

            }

        } else {

            $_SESSION['error'] = "Database query execution failed.";

        }

        $stmt->close();

    } else {

        $_SESSION['error'] = "Database statement preparation failed.";

    }

    $conn->close();

}



// If login failed, redirect back to login page

header("location: index.php"); 

exit;





?>
