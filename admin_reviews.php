<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database

// --- START: ACTION HANDLER (MOVED TO TOP) ---
// This logic now runs BEFORE any HTML is sent
$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $review_id = intval($_GET['id']);
    
    if ($action == 'review') {
        $sql = "UPDATE reviews SET admin_status = 'Reviewed' WHERE review_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $review_id);
            $stmt->execute();
            $stmt->close();
            // We set a session message because we are about to redirect
            $_SESSION['message'] = "Review marked as read."; 
        }
    }
    
    if ($action == 'delete') {
        $sql = "DELETE FROM reviews WHERE review_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $review_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "Review deleted successfully.";
        }
    }
    
    // We redirect to clear the URL parameters and show the message
    header("Location: admin_reviews.php");
    exit;
}
// --- END: ACTION HANDLER ---


// (Admin-Only) Guard this page
// This function MUST be called *after* all action logic
generate_header('Reviews', 'Admin');


// --- START: PAGE LOGIC (Fetch All Reviews) ---

// Check for success messages from the redirect
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = 'success';
    unset($_SESSION['message']); // Clear it so it doesn't show again
}

$reviews = [];
$sql = "SELECT 
            r.*, 
            u.full_name, 
            u.username 
        FROM 
            reviews r
        JOIN 
            users u ON r.user_id = u.user_id
        ORDER BY 
            r.admin_status = 'New' DESC, r.created_at DESC"; // Show 'New' reviews first

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    $result->free();
}
$conn->close();

// Helper function for topic color
function getTopicClass($type) {
    switch ($type) {
        case 'Suggestion':
            return 'bg-yellow-100 text-yellow-800';
        case 'Review':
            return 'bg-blue-100 text-blue-800';
        case 'Comment':
            return 'bg-purple-100 text-purple-800';
        case 'Feedback':
        default:
            return 'bg-green-100 text-green-800';
    }
}
// --- END: PAGE LOGIC ---
?>

<!-- This page is now DYNAMIC -->
<div class="bg-white p-6 rounded-xl shadow-lg">
    <h3 class="text-2xl font-bold mb-6">User Reviews & Feedback</h3>
    
    <!-- Display Success/Error Messages -->
    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="space-y-6">
    
        <?php if (empty($reviews)): ?>
            <div class="text-center text-gray-500 p-8">
                You have not received any reviews yet.
            </div>
        <?php endif; ?>
    
        <!-- START: Dynamic Review Loop -->
        <?php foreach ($reviews as $review): ?>
        <div class="border border-gray-200 rounded-lg p-6 <?= $review['admin_status'] == 'New' ? 'bg-white' : 'bg-gray-50 opacity-70' ?>">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <span class="font-bold text-gray-900"><?= htmlspecialchars($review['full_name']) ?> (<?= htmlspecialchars($review['username']) ?>)</span>
                    <span class="text-sm text-gray-500"> - <?= date('F j, Y, g:i a', strtotime($review['created_at'])) ?></span>
                </div>
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= getTopicClass($review['review_type']) ?>">
                    <?= htmlspecialchars($review['review_type']) ?>
                </span>
            </div>
            
            <p class="text-gray-700 mb-4">
                <?= nl2br(htmlspecialchars($review['message'])) ?>
            </p>
            
            <div class="flex space-x-4">
                <?php if ($review['admin_status'] == 'New'): ?>
                    <a href="admin_reviews.php?action=review&id=<?= $review['review_id'] ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800">Mark as Reviewed</a>
                <?php else: ?>
                    <span class="text-sm font-medium text-gray-400 cursor-not-allowed">Reviewed</span>
                <?php endif; ?>
                
                <button onclick="showDeleteModal('admin_reviews.php?action=delete&id=<?= $review['review_id'] ?>')" class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
            </div>
        </div>
        <?php endforeach; ?>
        <!-- END: Dynamic Review Loop -->
    
    </div>
</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>