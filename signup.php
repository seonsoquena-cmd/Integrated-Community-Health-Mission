<?php
// This MUST be the very first line
session_start();

require_once 'connection.php';

$error_message = '';
$purok_list = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6']; // Your barangay's puroks

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Use trim() to remove invisible whitespace
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $purok = trim($_POST['purok'] ?? ''); // NEW PUROK FIELD
    
    // --- Validation ---
    if (empty($full_name) || empty($username) || empty($password) || empty($confirm_password) || empty($purok)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match. Please try again.";
    } elseif (!in_array($purok, $purok_list)) {
        $error_message = "Invalid Purok selected.";
    } else {
        // --- Passed validation, proceed to create user AND patient ---
        
        // ** MODIFICATION: We are saving the plain text password. NO HASHING. **
        $role = 'Patient';
        
        // Start a transaction
        $conn->begin_transaction();
        
        try {
            // Step 1: Create the user
            $sql_user = "INSERT INTO users (username, password_text, full_name, role) VALUES (?, ?, ?, ?)";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("ssss", $username, $password, $full_name, $role);
            
            if (!$stmt_user->execute()) {
                if ($conn->errno == 1062) {
                    throw new Exception("Error: This username ('$username') already exists.");
                } else {
                    throw new Exception("Error: Could not create user account. " . $conn->error);
                }
            }
            
            // Step 2: Get the new user_id
            $user_id = $conn->insert_id;
            
            // Step 3: Create the patient profile
            $sql_patient = "INSERT INTO patients (user_id, full_name, purok) VALUES (?, ?, ?)";
            $stmt_patient = $conn->prepare($sql_patient);
            $stmt_patient->bind_param("iss", $user_id, $full_name, $purok);
            
            if (!$stmt_patient->execute()) {
                throw new Exception("Error: Could not create patient profile. " . $conn->error);
            }
            
            // If both were successful, commit the changes
            $conn->commit();
            
            // SUCCESS!
            $_SESSION['message'] = "Account created successfully! You can now log in.";
            header("location: index.php");
            exit;
            
        } catch (Exception $e) {
            // Something went wrong, roll back any changes
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | ICHM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="bg-white p-8 rounded-xl shadow-2xl border border-gray-100">
            <div class="flex flex-col items-center mb-6">
                <svg class="w-12 h-12 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <h2 class="text-3xl font-bold text-gray-800">Create New Account</h2>
                <p class="text-sm text-gray-500 mt-1">Register as a new patient</p>
            </div>

            <!-- Display Error Message -->
            <?php if (!empty($error_message)): ?>
                <div id="error-box" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm" role="alert">
                    <p class="font-bold">Registration Failed</p>
                    <p class="text-xs"><?= htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST" class="space-y-6">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Juan Dela Cruz">
                </div>
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., juandelacruz">
                </div>
                
                <!-- *** NEW PUROK DROPDOWN *** -->
                <div>
                    <label for="purok" class="block text-sm font-medium text-gray-700 mb-1">Purok</label>
                    <select id="purok" name="purok" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="" disabled selected>Select your Purok</option>
                        <?php foreach($purok_list as $p): ?>
                            <option value="<?= $p ?>"><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Create a secure password">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Type your password again">
                </div>
                
                <div class="text-sm text-center text-gray-500">
                    Already have an account yet? 
                    <a href="index.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Login Here
                    </a>
                </div>
                
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Sign Up
                </button>
            </form>
        </div>
    </div>
</body>
</html>