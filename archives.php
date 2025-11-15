<?php
// This page is PUBLIC. It uses the public layout.
require_once 'guest_layout.php';
require_once 'connection.php';

// Fetch all ARCHIVED announcements (is_archived = 1)
$announcements = [];
$sql = "SELECT a.*, u.full_name AS admin_name 
        FROM announcements a
        JOIN users u ON a.admin_id = u.user_id
        WHERE a.is_archived = 1
        ORDER BY a.created_at DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $result->free();
}
$conn->close();

// Start the public page layout
generate_public_header('Archives');
?>

<div class="space-y-8">

    <?php if (empty($announcements)): ?>
        <div class="bg-white p-8 rounded-lg shadow text-center">
            <h3 class="text-xl font-semibold text-gray-700">No Archived Posts</h3>
            <p class="text-gray-500 mt-2">There are no archived posts at this time.</p>
        </div>
    <?php endif; ?>

    <!-- Loop through each announcement and display it -->
    <?php foreach ($announcements as $post): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden opacity-80">
            <div class="p-6">
                <!-- Post Header -->
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <span class="w-12 h-12 rounded-full bg-gray-500 text-white flex items-center justify-center text-xl font-bold">A</span>
                    </div>
                    <div class="ml-4">
                        <div class="text-lg font-bold text-gray-900"><?= htmlspecialchars($post['admin_name']) ?></div>
                        <div class="text-sm text-gray-500">Archived on <?= date('F j, Y', strtotime($post['created_at'])) ?></div>
                    </div>
                </div>
                
                <!-- Post Content -->
                <h2 class="text-3xl font-bold text-gray-800 mb-3"><?= htmlspecialchars($post['title']) ?></h2>
                <div class="text-gray-700 leading-relaxed">
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