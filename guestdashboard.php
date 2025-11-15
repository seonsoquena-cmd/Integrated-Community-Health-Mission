<?php
// This page is PUBLIC. It uses the public layout.
require_once 'guest_layout.php';
require_once 'connection.php';

// Fetch all ACTIVE announcements (is_archived = 0)
$announcements = [];
$sql = "SELECT a.*, u.full_name AS admin_name 
        FROM announcements a
        JOIN users u ON a.admin_id = u.user_id
        WHERE a.is_archived = 0
        ORDER BY a.created_at DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $result->free();
}
$conn->close();

// Start the public page layout
generate_public_header('Announcements');
?>

<div class="space-y-8">

    <?php if (empty($announcements)): ?>
        <div class="bg-white p-8 rounded-lg shadow text-center">
            <h3 class="text-xl font-semibold text-gray-700">No Announcements</h3>
            <p class="text-gray-500 mt-2">There are no active announcements at this time. Please check back later!</p>
        </div>
    <?php endif; ?>

    <!-- Loop through each announcement and display it -->
    <?php foreach ($announcements as $post): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Post Image -->
            <?php if (!empty($post['image_path'])): ?>
                <!-- This will show a placeholder if the image is missing -->
                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" 
                     class="w-full h-64 object-cover"
                     onerror="this.src='images/Mission Sample.jpg'; this.onerror=null;">
            <?php endif; ?>
            
            <div class="p-6">
                <!-- Post Header -->
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <span class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl font-bold">A</span>
                    </div>
                    <div class="ml-4">
                        <div class="text-lg font-bold text-gray-900"><?= htmlspecialchars($post['admin_name']) ?></div>
                        <div class="text-sm text-gray-500">Posted on <?= date('F j, Y \a\t g:i a', strtotime($post['created_at'])) ?></div>
                    </div>
                </div>
                
                <!-- Post Content -->
                <h2 class="text-3xl font-bold text-gray-800 mb-3"><?= htmlspecialchars($post['title']) ?></h2>
                <div class="text-gray-700 leading-relaxed">
                    <!-- Use nl2br to respect line breaks in the post content -->
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<?php
// Close the public page layout
generate_public_footer();
?>