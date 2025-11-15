<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database connection

// (Admin-Only) Guard this page
generate_header('Dashboard', 'Admin');

// --- START: PAGE LOGIC ---

$message = '';
$message_type = ''; // 'success' or 'error'

// --- 1. HANDLE POST REQUEST (CREATE ANNOUNCEMENT) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_post'])) {
    $title = trim($_POST['post_title'] ?? '');
    $content = trim($_POST['post_content'] ?? '');
    $admin_id = $_SESSION['user_id'];
    $image_path = null;

    // --- Image Upload Logic ---
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        $target_dir = "images/"; // The folder we created
        $target_file = $target_dir . basename($_FILES["post_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image
        $check = getimagesize($_FILES["post_image"]["tmp_name"]);
        if($check !== false) {
            // Check file size (e.g., 5MB limit)
            if ($_FILES["post_image"]["size"] > 9000000) {
                $message = "Error: Your file is too large (5MB limit).";
                $message_type = 'error';
            } else {
                // Allow certain file formats
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                    $message = "Error: Only JPG, JPEG, PNG & GIF files are allowed.";
                    $message_type = 'error';
                } else {
                    // Try to move the uploaded file
                    if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)) {
                        $image_path = $target_file;
                    } else {
                        $message = "Error: There was an error uploading your file.";
                        $message_type = 'error';
                    }
                }
            }
        } else {
            $message = "Error: File is not an image.";
            $message_type = 'error';
        }
    }

    // --- Insert into Database (if no upload error) ---
    if (empty($message) && !empty($title) && !empty($content)) {
        $sql = "INSERT INTO announcements (admin_id, title, content, image_path) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isss", $admin_id, $title, $content, $image_path);
            if ($stmt->execute()) {
                $message = "Announcement posted successfully!";
                $message_type = 'success';
            } else {
                $message = "Error: Could not save post to database.";
                $message_type = 'error';
            }
            $stmt->close();
        }
    } elseif (empty($title) || empty($content)) {
        $message = "Error: Title and Content are required.";
        $message_type = 'error';
    }
}

// --- 2. HANDLE GET REQUESTS (DELETE, ARCHIVE, UN-ARCHIVE) ---
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    $action = $_GET['action'];
    $post_id = $_GET['id'] ?? 0;

    if ($action == 'delete' && $post_id > 0) {
        // TODO: Also delete the image file from the 'uploads/' folder
        $sql = "DELETE FROM announcements WHERE announcement_id = ? AND admin_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $message = "Post deleted successfully.";
            $message_type = 'success';
        }
    }
    
    if ($action == 'archive' && $post_id > 0) {
        $sql = "UPDATE announcements SET is_archived = 1 WHERE announcement_id = ? AND admin_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $message = "Post archived successfully.";
            $message_type = 'success';
        }
    }
    
    if ($action == 'unarchive' && $post_id > 0) {
        $sql = "UPDATE announcements SET is_archived = 0 WHERE announcement_id = ? AND admin_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $message = "Post restored successfully.";
            $message_type = 'success';
        }
    }
}


// --- 3. FETCH ALL POSTS TO DISPLAY ---
$posts = [];
$sql = "SELECT a.*, u.full_name AS admin_name 
        FROM announcements a
        JOIN users u ON a.admin_id = u.user_id
        ORDER BY a.created_at DESC";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $result->free();
}
$conn->close();

?>

<!-- 
This page is for creating and managing announcements.
It is now fully functional.
-->

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Column 1: Create Post -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-xl shadow-lg sticky top-6">
            <h3 class="text-2xl font-bold mb-6">Create Announcement</h3>
            
            <!-- Display Success/Error Messages -->
            <?php if (!empty($message)): ?>
                <div class="mb-4 p-4 rounded-lg <?= $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <!-- This form now submits to itself and allows file uploads -->
            <form action="admindashboard.php" method="POST" class="space-y-4" enctype="multipart/form-data">
                <input type="hidden" name="create_post" value="1">
                <div>
                    <label for="post_title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="post_title" name="post_title" 
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Upcoming Medical Mission" required>
                </div>
            
                <div>
                    <label for="post_content" class="block text-sm font-medium text-gray-700">Content</label>
                    <textarea id="post_content" name="post_content" rows="6"
                              class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="What's on your mind, Admin?" required></textarea>
                </div>
                
                <div>
                    <label for="post_image" class="block text-sm font-medium text-gray-700">Upload Image (Optional)</label>
                    <input type="file" id="post_image" name="post_image" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Post Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Column 2: Post Feed -->
    <div class="lg:col-span-2 space-y-8">
        
        <?php if (empty($posts)): ?>
            <div class="bg-white p-8 rounded-lg shadow text-center">
                <h3 class="text-xl font-semibold text-gray-700">No Announcements Yet</h3>
                <p class="text-gray-500 mt-2">Use the form on the left to create your first post!</p>
            </div>
        <?php endif; ?>
        
        <!-- DYNAMIC POST LOOP -->
        <?php foreach ($posts as $post): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden <?= $post['is_archived'] ? 'opacity-60' : '' ?>">
                
                <?php if (!empty($post['image_path'])): ?>
                    <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" 
                         class="w-full h-64 object-cover"
                         onerror="this.src='https://placehold.co/1000x400/EBF8FF/3182CE?text=Image+Not+Found'; this.onerror=null;">
                <?php endif; ?>
                
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <span class="w-12 h-12 rounded-full <?= $post['is_archived'] ? 'bg-gray-500' : 'bg-blue-600' ?> text-white flex items-center justify-center text-xl font-bold">A</span>
                        <div class="ml-4">
                            <div class="text-lg font-bold text-gray-900"><?= htmlspecialchars($post['admin_name']) ?></div>
                            <div class="text-sm text-gray-500">
                                Posted on <?= date('F j, Y', strtotime($post['created_at'])) ?>
                                <?php if ($post['is_archived']): ?>
                                    <span class="font-bold text-yellow-600">(Archived)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <h2 class="text-3xl font-bold text-gray-800 mb-3"><?= htmlspecialchars($post['title']) ?></h2>
                    <div class="text-gray-700 leading-relaxed">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>
                    
                    <!-- Post Actions -->
                    <div class="mt-6 pt-4 border-t border-gray-200 flex space-x-4">
                        <a href="edit_post.php?id=<?= $post['announcement_id'] ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800">Edit</a>
                        
                        <!-- This button now calls the JavaScript delete modal -->
                        <button onclick="showDeleteModal('admindashboard.php?action=delete&id=<?= $post['announcement_id'] ?>')" class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                        
                        <?php if ($post['is_archived']): ?>
                            <a href="admindashboard.php?action=unarchive&id=<?= $post['announcement_id'] ?>" class="text-sm font-medium text-gray-600 hover:text-gray-800">Un-Archive</a>
                        <?php else: ?>
                            <a href="admindashboard.php?action=archive&id=<?= $post['announcement_id'] ?>" class="text-sm font-medium text-gray-600 hover:text-gray-800">Archive</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <!-- END DYNAMIC POST LOOP -->

    </div>
</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>