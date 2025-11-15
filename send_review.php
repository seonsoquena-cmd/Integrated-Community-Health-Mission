<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database

// (Patient-Only) Guard this page
generate_header('Send Review', 'Patient');

// --- START: PAGE LOGIC ---
$message = '';
$message_type = ''; // 'success' or 'error'

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $review_type = trim($_POST['review_type'] ?? '');
    $review_message = trim($_POST['message'] ?? '');
    
    // Validation
    if (empty($review_type) || empty($review_message)) {
        $message = "Please select a topic and write a message.";
        $message_type = 'error';
    } else {
        // All good, insert into the database
        $sql = "INSERT INTO reviews (user_id, review_type, message, admin_status) VALUES (?, ?, ?, 'New')";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iss", $user_id, $review_type, $review_message);
            if ($stmt->execute()) {
                $message = "Thank you! Your feedback has been submitted successfully.";
                $message_type = 'success';
            } else {
                $message = "Error: Could not submit your review. " . $conn->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">

    <h3 class="text-3xl font-bold text-gray-800 mb-4">Send Feedback</h3>
    <p class="text-gray-600 mb-6">We value your opinion. Please let us know about your experience or any suggestions you have.</p>

    <!-- Display Success/Error Messages -->
    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- This form is now fully functional -->
    <form action="send_review.php" method="POST" class="space-y-6">
    
        <div>
            <label for="review_type" class="block text-sm font-medium text-gray-700">Topic / Type</label>
            <select id="review_type" name="review_type" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="" disabled selected>Select a topic</option>
                <option value="Feedback">General Feedback</option>
                <option value="Suggestion">Suggestion</option>
                <option value="Review">Review of Health Worker</option>
                <option value="Comment">Comment on Mission</option>
            </select>
        </div>
    
        <div>
            <label for="message" class="block text-sm font-medium text-gray-700">Your Message</label>
            <textarea id="message" name="message" rows="6" required
                      class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Please type your honest feedback here..."></textarea>
        </div>
        
        <div>
            <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Submit Review
            </button>
        </div>
    </form>

</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>