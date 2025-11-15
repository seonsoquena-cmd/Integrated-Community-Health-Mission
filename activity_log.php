<?php
// This line includes the authentication guard.
// It also starts the session.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database

// --- START: ACTION HANDLER (MOVED TO TOP) ---
// This logic now runs BEFORE any HTML is sent
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'clear') {
    // Double-check that only Admins can perform this
    if ($_SESSION['role'] == 'Admin') {
        
        // TRUNCATE TABLE is faster than DELETE FROM and resets the auto-increment counter
        $conn->query("TRUNCATE TABLE activity_log");
        
        // This header() call will now work perfectly
        header("Location: activity_log.php");
        exit;
    }
}
// --- END: ACTION HANDLER ---


// (Admin-Only) Guard this page
// This function MUST be called *after* all action logic
generate_header('Activity Log', 'Admin');


// --- START: PAGE LOGIC (FETCHING DATA) ---

// 1. Fetch all activity logs, joining with the users table
$logs = [];
$sql = "SELECT 
            log.activity_type, 
            log.timestamp, 
            u.username, 
            u.full_name, 
            u.role 
        FROM 
            activity_log AS log
        JOIN 
            users AS u ON log.user_id = u.user_id
        ORDER BY 
            log.timestamp DESC
        LIMIT 50"; // Get the 50 most recent activities

// We must re-use the $conn variable. 
// It's safer to let PHP close the connection at the end of the script.
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    $result->free();
}
// We don't close the connection here, in case the footer needs it.

// 2. A helper function to get the color for each role
function getRoleClass($role) {
    switch ($role) {
        case 'Admin':
            return 'bg-red-100 text-red-800';
        case 'Health Worker':
            return 'bg-blue-100 text-blue-800';
        case 'Patient':
            return 'bg-green-100 text-green-800';
        case 'Guest':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

?>

<!-- This page is now DYNAMIC -->
<div class="bg-white p-6 rounded-xl shadow-lg">

    <!-- *** Header with Buttons *** -->
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-6">
        <h3 class="text-2xl font-bold mb-4 md:mb-0">Recent User Activity</h3>
        <div class="flex space-x-3">
            <!-- This link just reloads the page, fetching new data -->
            <a href="activity_log.php" class="py-2 px-4 bg-blue-100 text-blue-700 font-medium rounded-lg hover:bg-blue-200 transition">
                Refresh
            </a>
            <!-- This button calls the delete modal from authentication.php -->
            <button onclick="showDeleteModal('activity_log.php?action=clear')" class="py-2 px-4 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                Clear Log
            </button>
        </div>
    </div>
    <!-- *** END: New Header *** -->
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                
                <!-- Check if any logs were found -->
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No user activity has been recorded yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <!-- START: Dynamic Log Loop -->
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowGrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($log['full_name']) ?></div>
                            <div class="text-sm text-gray-500">@<?= htmlspecialchars($log['username']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= getRoleClass($log['role']) ?>">
                                <?= htmlspecialchars($log['role']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900"><?= htmlspecialchars($log['activity_type']) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <!-- Format the timestamp to be readable -->
                            <div class="text-sm text-gray-900"><?= date('F j, Y, g:i a', strtotime($log['timestamp'])) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <!-- END: Dynamic Log Loop -->

            </tbody>
        </table>
    </div>
</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>