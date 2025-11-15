<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database connection

// (Admin-Only) Guard this page
generate_header('Edit Announcement', 'Admin');

// --- START: PAGE LOGIC ---

$message = '';
$message_type = ''; // 'success' or 'error'
$post_id = $_GET['id'] ?? 0;
$post = null;

// --- 1. HANDLE POST REQUEST (SAVE CHANGES) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_post'])) {
    $title = trim($_POST['post_title'] ?? '');
    $content = trim($_POST['post_content'] ?? '');
    $post_id_to_update = $_POST['post_id'] ?? 0;
    
    // Simple validation
    if (!empty($title) && !empty($content) && $post_id_to_update > 0) {
        // We are NOT handling image updates in this simple form to avoid complexity
        // We are only updating text content
        
        $sql = "UPDATE announcements SET title = ?, content = ? WHERE announcement_id = ? AND admin_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $title, $content, $post_id_to_update, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $message = "Post updated successfully!";
                $message_type = 'success';
            } else {
                $message = "Error: Could not update post.";
                $message_type = 'error';
            }
            $stmt->close();
        }
    } else {
        $message = "Error: Title and Content are required.";
        $message_type = 'error';
    }
}

// --- 2. FETCH THE POST TO EDIT (based on URL ?id=X) ---
if ($post_id > 0) {
    $sql = "SELECT * FROM announcements WHERE announcement_id = ? AND admin_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $post = $result->fetch_assoc();
            } else {
                $message = "Error: Post not found or you do not have permission to edit it.";
                $message_type = 'error';
            }
        }
        $stmt->close();
    }
} else {
    $message = "Error: No post ID provided.";
    $message_type = 'error';
}

$conn->close();

?>

<!-- 
This page is for editing an announcement.
-->

<div class="max-w-3xl mx-auto">
    <!-- Back link -->
    <a href="admindashboard.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to Dashboard
    </a>

    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-3xl font-bold mb-6">Edit Announcement</h3>

        <!-- Display Success/Error Messages -->
        <?php if (!empty($message)): ?>
            <div class="mb-6 p-4 rounded-lg <?= $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- If the post was found, show the form -->
        <?php if ($post): ?>
        <form action="edit_post.php?id=<?= $post['announcement_id'] ?>" method="POST" class="space-y-4">
            <input type="hidden" name="update_post" value="1">
            <input type="hidden" name="post_id" value="<?= $post['announcement_id'] ?>">
            
            <div>
                <label for="post_title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" id="post_title" name="post_title" 
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>
        
            <div>
                <label for="post_content" class="block text-sm font-medium text-gray-700">Content</label>
                <textarea id="post_content" name="post_content" rows="10"
                          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                          required><?= htmlspecialchars($post['content']) ?></textarea>
            </div>
            
            <!-- Image editing is disabled for simplicity -->
            <?php if (!empty($post['image_path'])): ?>
            <div>
                <label class="block text-sm font-medium text-gray-700">Current Image</label>
                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Current Image" class="mt-2 rounded-lg border border-gray-200 w-full max-w-xs">
                <p class="text-xs text-gray-500 mt-2">Note: Image uploading is disabled on the edit page for simplicity. To change the image, please delete this post and create a new one.</p>
            </div>
            <?php endif; ?>
            
            <div>
                <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Changes
                </button>
            </div>
        </form>
        <?php endif; ?>
        
    </div>
</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>