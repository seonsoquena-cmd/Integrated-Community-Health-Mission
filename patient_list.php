<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database

// (Health Worker-Only) Guard this page
generate_header('List of Patients', 'Health Worker');

// --- START: ACTION HANDLER (DELETE) ---
$message = $_SESSION['message'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $patient_id_to_delete = intval($_GET['id']);
    
    if ($_SESSION['role'] == 'Health Worker' || $_SESSION['role'] == 'Admin') {
        
        // We need to delete the USER, not the patient, to cascade correctly.
        $user_id_to_delete = null;
        $sql_find = "SELECT user_id FROM patients WHERE patient_id = ?";
        if ($stmt_find = $conn->prepare($sql_find)) {
            $stmt_find->bind_param("i", $patient_id_to_delete);
            $stmt_find->execute();
            $stmt_find->bind_result($user_id_to_delete);
            $stmt_find->fetch();
            $stmt_find->close();
        }

        if ($user_id_to_delete) {
            // Delete the user from the USERS table.
            // CASCADES will delete from: patients, reviews, activity_log
            // SET NULL will apply to: health_records (hw_id), follow_ups (hw_id)
            $sql_delete = "DELETE FROM users WHERE user_id = ?";
            if ($stmt_delete = $conn->prepare($sql_delete)) {
                $stmt_delete->bind_param("i", $user_id_to_delete);
                if ($stmt_delete->execute()) {
                    $_SESSION['message'] = "Patient and all associated records deleted successfully.";
                } else {
                    $_SESSION['error'] = "Error: Could not delete user. " . $conn->error;
                }
                $stmt_delete->close();
            }
        } else {
            $_SESSION['error'] = "Error: Could not find user associated with patient ID.";
        }
    } else {
        $_SESSION['error'] = "You are not authorized to perform this action.";
    }
    
    // Redirect back to the list to see the changes
    header("Location: patient_list.php");
    exit;
}
// --- END: ACTION HANDLER ---


// --- START: PAGE LOGIC (FETCHING) ---
// This code runs *after* the delete logic (if any)

// 1. Get filter values from the URL
$search_query = trim($_GET['search'] ?? '');
$filter_purok = trim($_GET['filter_purok'] ?? 'All Puroks');
$sort = trim($_GET['sort'] ?? 'Name (A-Z)');

// 2. Build the dynamic SQL query
$sql = "SELECT patient_id, user_id, full_name, dob, address, purok FROM patients WHERE 1=1";
$params = []; 
$types = ""; 

// Add search
if (!empty($search_query)) {
    $sql .= " AND full_name LIKE ?";
    $params[] = "%" . $search_query . "%";
    $types .= "s";
}

// Add purok filter
if ($filter_purok != 'All Puroks') {
    $sql .= " AND purok = ?";
    $params[] = $filter_purok;
    $types .= "s";
}

// Add sorting
if ($sort == 'Name (Z-A)') {
    $sql .= " ORDER BY purok, full_name DESC";
} else {
    // Default sort
    $sql .= " ORDER BY purok, full_name ASC";
}

// 3. Fetch data from the database
$patients_from_db = [];
// We re-use the $conn variable from the top of the script
if ($stmt = $conn->prepare($sql)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $patients_from_db[] = $row;
    }
    $stmt->close();
}
$conn->close();

// 4. Group the results by Purok
$grouped_patients = [];
foreach ($patients_from_db as $patient) {
    $purok = $patient['purok'] ?? 'Unassigned';
    if (!isset($grouped_patients[$purok])) {
        $grouped_patients[$purok] = [];
    }
    $grouped_patients[$purok][] = $patient;
}
$purok_list = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6'];

?>

<div class="space-y-8">

    <!-- Search and Filter Bar -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="patient_list.php" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-grow">
                <label for="search_patient" class="block text-sm font-medium text-gray-700">Search Patient</label>
                <input type="text" id="search_patient" name="search" 
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Enter patient name..." value="<?= htmlspecialchars($search_query) ?>">
            </div>
            <div>
                <label for="filter_purok" class="block text-sm font-medium text-gray-700">Filter by Purok</label>
                <select id="filter_purok" name="filter_purok" 
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option>All Puroks</option>
                    <?php foreach($purok_list as $p): ?>
                        <option <?= $filter_purok == $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="filter_sort" class="block text-sm font-medium text-gray-700">Sort by</label>
                <select id="filter_sort" name="sort" 
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option <?= $sort == 'Name (A-Z)' ? 'selected' : '' ?>>Name (A-Z)</option>
                    <option <?= $sort == 'Name (Z-A)' ? 'selected' : '' ?>>Name (Z-A)</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full md:w-auto py-2 px-5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Success/Error Message Box -->
    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-lg bg-green-100 text-green-800">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="p-4 rounded-lg bg-red-100 text-red-800">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    
    <!-- Debug Box to show filters -->
    <?php if (!empty($search_query) || $filter_purok != 'All Puroks'): ?>
        <div class="p-4 rounded-lg bg-yellow-100 text-yellow-800 text-sm">
            <strong>Filter active:</strong> 
            Searching for "<?= htmlspecialchars($search_query) ?>" in "<?= htmlspecialchars($filter_purok) ?>", 
            sorted <?= htmlspecialchars($sort) ?>. 
            (Found <?= count($patients_from_db) ?> results).
        </div>
    <?php endif; ?>

    <!-- Patient List -->
    <div class="space-y-6">
    
        <?php if (empty($grouped_patients) && (empty($search_query) && $filter_purok == 'All Puroks')): ?>
            <div class="bg-white p-8 rounded-lg shadow text-center">
                <h3 class="text-xl font-semibold text-gray-700">No Patients Found</h3>
                <p class="text-gray-500 mt-2">The `patients` table is empty. Try creating a new patient account.</p>
            </div>
        <?php elseif (empty($grouped_patients)): ?>
            <div class="bg-white p-8 rounded-lg shadow text-center">
                <h3 class="text-xl font-semibold text-gray-700">No Patients Found</h3>
                <p class="text-gray-500 mt-2">No patients match your current search and filter criteria.</p>
            </div>
        <?php endif; ?>
    
        <?php foreach ($grouped_patients as $purok => $list): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <h3 class="text-xl font-bold text-gray-900 bg-gray-50 p-4 border-b border-gray-200"><?= htmlspecialchars($purok) ?> (<?= count($list) ?> patients)</h3>
            
            <div class="divide-y divide-gray-200">
                <?php foreach ($list as $patient): ?>
                <!-- Patient Row -->
                <div class="p-4 flex flex-col md:flex-row justify-between md:items-center hover:bg-gray-50">
                    <div>
                        <p class="text-lg font-bold text-blue-700"><?= htmlspecialchars($patient['full_name']) ?></p>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($patient['address'] ?? 'No address') ?></p>
                        <p class="text-sm text-gray-500">DOB: <?= htmlspecialchars($patient['dob'] ?? 'N/A') ?></p>
                    </div>
                    <!-- Actions -->
                    <div class="flex space-x-2 mt-4 md:mt-0">
                        <a href="edit_patient_record.php?id=<?= $patient['patient_id'] ?>" class="py-2 px-4 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                            View / Edit Record
                        </a>
                        <button onclick="showDeleteModal('patient_list.php?action=delete&id=<?= $patient['patient_id'] ?>')" class="py-2 px-4 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition">
                            Delete
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>