<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php';

// Set initial message variables
$message = '';
$message_type = ''; // 'success' or 'error'
$purok_list = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Only Admins can process this form
    if ($_SESSION["role"] == 'Admin') {
        
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $purok = trim($_POST['purok'] ?? ''); // NEW PUROK FIELD
        
        if (empty($full_name) || empty($username) || empty($password) || empty($role)) {
            $message = "All fields are required (Purok is only required for Patients).";
            $message_type = 'error';
        } elseif ($role == 'Patient' && empty($purok)) {
            $message = "Purok is required when creating a Patient account.";
            $message_type = 'error';
        } else {
            
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
                
                // Step 2: If the role is 'Patient', create the patient profile
                if ($role == 'Patient') {
                    $user_id = $conn->insert_id; // Get the user_id we just created
                    
                    $sql_patient = "INSERT INTO patients (user_id, full_name, purok) VALUES (?, ?, ?)";
                    $stmt_patient = $conn->prepare($sql_patient);
                    $stmt_patient->bind_param("iss", $user_id, $full_name, $purok);
                    
                    if (!$stmt_patient->execute()) {
                        throw new Exception("Error: User was created, but patient profile failed. " . $conn->error);
                    }
                }
                
                // If everything was successful, commit
                $conn->commit();
                $message = "User created successfully! ($full_name as $role)";
                $message_type = 'success';
                
            } catch (Exception $e) {
                // Something went wrong, roll back
                $conn->rollback();
                $message = $e->getMessage();
                $message_type = 'error';
            }
        }
    } else {
        $message = "You are not authorized to perform this action.";
        $message_type = 'error';
    }
}


// (Admin-Only) Guard this page
generate_header('Create New User (Admin)', 'Admin');

?>

<!-- This is the start of your page's HTML -->
<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">

    <!-- Display Success/Error Messages -->
    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <h3 class="text-2xl font-bold mb-6">Create New Staff/User Account</h3>

    <form action="create_user.php" method="POST" class="space-y-6">
        <div>
            <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" id="full_name" name="full_name" required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" id="username" name="username" required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- THIS IS THE ROLE DROPDOWN -->
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
            <select id="role" name="role" required
                    onchange="togglePurokField(this.value)"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="" disabled selected>Select a role</option>
                <option value="Admin">Admin</option>
                <option value="Health Worker">Health Worker</option>
                <option value="Patient">Patient</option>
                <option value="Guest">Guest</option>
            </select>
        </div>
        
        <!-- *** NEW PUROK FIELD (HIDDEN BY DEFAULT) *** -->
        <div id="purok-field" class="space-y-1" style="display: none;">
            <label for="purok" class="block text-sm font-medium text-gray-700">Purok (Required for Patients)</label>
            <select id="purok" name="purok" 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="" disabled selected>Select a Purok</option>
                <?php foreach($purok_list as $p): ?>
                    <option value="<?= $p ?>"><?= $p ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Create Account
            </button>
        </div>
    </form>
</div>

<script>
    function togglePurokField(selectedRole) {
        const purokField = document.getElementById('purok-field');
        if (selectedRole === 'Patient') {
            purokField.style.display = 'block';
        } else {
            purokField.style.display = 'none';
        }
    }
</script>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>