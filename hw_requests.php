<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php';

// (Health Worker-Only) Guard this page
generate_header('Requests', 'Health Worker');

// --- START: ACTION HANDLER (Approve/Reject) ---
$message = '';
$message_type = '';

$hw_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $hw_mission_id = intval($_GET['id']);
    $new_status = '';
    
    if ($action == 'approve') {
        $new_status = 'Approved';
    } elseif ($action == 'reject') {
        $new_status = 'Rejected';
    }
    
    if (!empty($new_status)) {
        // Update the status in the junction table
        $sql_update = "UPDATE hw_missions SET status = ? WHERE hw_mission_id = ? AND hw_id = ?";
        if ($stmt = $conn->prepare($sql_update)) {
            $stmt->bind_param("sii", $new_status, $hw_mission_id, $hw_id);
            if ($stmt->execute()) {
                $message = "Request has been $new_status.";
                $message_type = 'success';
            } else {
                $message = "Error updating request.";
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}
// --- END: ACTION HANDLER ---

// --- START: PAGE LOGIC (Fetch Requests) ---
$requests = [];
$sql = "SELECT 
            m.title, 
            m.details, 
            m.created_at, 
            u.full_name AS admin_name,
            hm.hw_mission_id, 
            hm.status
        FROM 
            missions m
        JOIN 
            hw_missions hm ON m.mission_id = hm.mission_id
        JOIN 
            users u ON m.admin_id = u.user_id
        WHERE 
            hm.hw_id = ?
        ORDER BY 
            hm.status = 'Pending' DESC, m.created_at DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $hw_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $stmt->close();
}
$conn->close();

// Helper function for status color
function getStatusClass($status) {
    switch ($status) {
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'Approved':
            return 'bg-green-100 text-green-800';
        case 'Rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>

<div class="space-y-6">

    <!-- Display Success/Error Messages -->
    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="bg-white p-8 rounded-lg shadow text-center">
            <h3 class="text-xl font-semibold text-gray-700">No Requests Found</h3>
            <p class="text-gray-500 mt-2">You do not have any mission requests from an admin at this time.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($requests as $request): ?>
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex flex-col md:flex-row justify-between md:items-start">
            <div>
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?= getStatusClass($request['status']) ?>">
                    Status: <?= htmlspecialchars($request['status']) ?>
                </span>
                <h3 class="text-2xl font-bold text-gray-800 mt-3"><?= htmlspecialchars($request['title']) ?></h3>
                <p class="text-sm text-gray-500">Sent by <?= htmlspecialchars($request['admin_name']) ?> on <?= date('F j, Y', strtotime($request['created_at'])) ?></p>
            </div>
            
            <!-- Actions for Pending requests -->
            <?php if ($request['status'] == 'Pending'): ?>
            <div class="flex space-x-3 mt-4 md:mt-0">
                <a href="hw_requests.php?action=approve&id=<?= $request['hw_mission_id'] ?>" class="py-2 px-5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                    Approve
                </a>
                <a href="hw_requests.php?action=reject&id=<?= $request['hw_mission_id'] ?>" class="py-2 px-5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                    Reject
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-4 border-t pt-4">
            <label class="text-sm font-medium text-gray-500">Request Details</label>
            <p class="text-md text-gray-800 p-4 bg-gray-50 rounded-lg mt-2">
                <?= nl2br(htmlspecialchars($request['details'])) ?>
            </p>
        </div>
    </div>
    <?php endforeach; ?>
    
</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>