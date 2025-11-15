<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php';

// (Admin-Only) Guard this page
generate_header('Send Request', 'Admin');

// --- START: PAGE LOGIC ---

$message = '';
$message_type = ''; // 'success' or 'error'

// 1. Fetch all Health Workers to populate the dropdown
$health_workers = [];
$sql_hw = "SELECT user_id, full_name FROM users WHERE role = 'Health Worker' ORDER BY full_name";
if ($result = $conn->query($sql_hw)) {
    while ($row = $result->fetch_assoc()) {
        $health_workers[] = $row;
    }
    $result->free();
}

// 2. Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_request'])) {
    $admin_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $hw_id = $_POST['hw_id'] ?? 0;
    
    // Validation
    if (empty($title) || empty($details) || empty($hw_id)) {
        $message = "All fields are required.";
        $message_type = 'error';
    } else {
        // We use a transaction to create the mission AND assign it
        $conn->begin_transaction();
        try {
            // Step 1: Create the master mission
            $sql_mission = "INSERT INTO missions (admin_id, title, details) VALUES (?, ?, ?)";
            $stmt_mission = $conn->prepare($sql_mission);
            $stmt_mission->bind_param("iss", $admin_id, $title, $details);
            if (!$stmt_mission->execute()) {
                throw new Exception("Failed to create mission: " . $conn->error);
            }
            
            // Get the new mission_id
            $mission_id = $conn->insert_id;
            
            // Step 2: Assign it to the health worker in the junction table
            $sql_hw_mission = "INSERT INTO hw_missions (mission_id, hw_id, status) VALUES (?, ?, 'Pending')";
            $stmt_hw = $conn->prepare($sql_hw_mission);
            $stmt_hw->bind_param("ii", $mission_id, $hw_id);
            if (!$stmt_hw->execute()) {
                throw new Exception("Failed to assign mission to health worker: " . $conn->error);
            }
            
            // If both succeed, commit the transaction
            $conn->commit();
            $message = "Request sent successfully!";
            $message_type = 'success';
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = $e->getMessage();
            $message_type = 'error';
        }
    }
}

$conn->close();
?>

<!-- This is the start of your page's HTML -->
<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">

    <h3 class="text-3xl font-bold text-gray-800 mb-6">Send Mission Request</h3>

    <!-- Display Success/Error Messages -->
    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="send_request.php" method="POST" class="space-y-6">
        <input type="hidden" name="send_request" value="1">
        
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Mission Title</label>
            <input type="text" id="title" name="title" required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Purok 3 Vaccine Drive">
        </div>
        
        <div>
            <label for="hw_id" class="block text-sm font-medium text-gray-700">Assign to Health Worker</label>
            <select id="hw_id" name="hw_id" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="" disabled selected>Select a Health Worker</option>
                <?php foreach($health_workers as $hw): ?>
                    <option value="<?= $hw['user_id'] ?>"><?= htmlspecialchars($hw['full_name']) ?></option>
                <?php endforeach; ?>
                <?php if(empty($health_workers)): ?>
                    <option value="" disabled>No health workers found. Please create one first.</option>
                <?php endif; ?>
            </select>
        </div>
        
        <div>
            <label for="details" class="block text-sm font-medium text-gray-700">Details / Instructions</label>
            <textarea id="details" name="details" rows="5" required
                      class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="e.g., We need 2 HWs for a flu vaccine drive..."></textarea>
        </div>
        
        <div>
            <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Send Request
            </button>
        </div>
    </form>
</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>