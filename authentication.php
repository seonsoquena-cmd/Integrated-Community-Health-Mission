<?php
// This MUST be the very first line of this file.
session_start();

// Ensure the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $_SESSION['error'] = "You must be logged in to view that page.";
    header("location: index.php"); // Redirect to login page
    exit;
}

// Global function to generate the page header
function generate_header($page_title, $required_role = null) {
    // Role-based Access Control (RBAC) Check
    if ($required_role && $_SESSION["role"] !== $required_role) {
        $_SESSION['error'] = "You are not authorized to view that page.";
        header("location: index.php"); 
        exit;
    }

    $full_name = htmlspecialchars($_SESSION["full_name"]);
    $role = htmlspecialchars($_SESSION["role"]);

    // Define navigation links
    $nav_links = get_nav_links($role);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Integrated Health Mission</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-50 flex">
    <!-- Sidebar -->
    <aside class="sidebar w-64 h-screen bg-blue-700 text-white shadow-xl flex-shrink-0 hidden md:block">
        <div class="p-6">
            <h1 class="text-3xl font-extrabold tracking-tight">ICHM</h1>
            <p class="text-sm opacity-75 mt-1">Barangay Health</p>
        </div>
        <nav class="mt-8 space-y-2 px-4">
            <?php foreach ($nav_links as $link): ?>
                <a href="<?= $link['url'] ?>" 
                   id="<?= $link['name'] == 'Logout' ? 'logout-btn' : '' ?>"
                   class="flex items-center p-3 rounded-lg text-sm font-medium 
                   <?= $link['name'] == 'Logout' ? 'text-blue-200 hover:bg-red-500' : 'hover:bg-blue-600' ?> 
                   transition duration-150 <?= $page_title == $link['name'] ? 'bg-blue-800 shadow-md' : '' ?>">
                    
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><?= $link['icon'] ?></svg>
                    <?= $link['name'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
        
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto">
        <!-- Main Header -->
        <div class="p-6 md:p-8 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-4xl font-extrabold text-gray-800"><?= $page_title ?></h2>
                <div class="text-right">
                    <p class="text-lg font-semibold text-gray-700">Welcome, <?= $full_name ?>!</p>
                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                        Role: <?= $role ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Start of Page Specific Content -->
        <div class="p-6 md:p-8">
<?php
}

// Function to close the main content wrapper
function generate_footer() {
?>
        </div>
    </main>
    
    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" style="display: none; z-index: 1000;">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Confirm Deletion</h3>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this item? This action cannot be undone.</p>
            <div class="flex justify-end space-x-4">
                <button id="delete-cancel-btn" class="py-2 px-6 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
                <a id="delete-confirm-btn" class="py-2 px-6 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition cursor-pointer">
                    Delete
                </a>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" style="display: none; z-index: 999;">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Confirm Logout</h3>
                <button id="modal-close-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to log out of your account?</p>
            <div class="flex justify-end space-x-4">
                <button id="modal-cancel-btn" class="py-2 px-6 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
                <a href="logout.php" id="modal-confirm-btn" class="py-2 px-6 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                    Logout
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // --- JavaScript for Logout Modal ---
        const logoutBtn = document.getElementById('logout-btn');
        const logoutModal = document.getElementById('logout-modal');
        const closeModalBtn = document.getElementById('modal-close-btn');
        const cancelModalBtn = document.getElementById('modal-cancel-btn');

        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Stop the link from navigating
                logoutModal.style.display = 'flex';
            });
        }
        
        function closeLogoutModal() {
            if (logoutModal) logoutModal.style.display = 'none';
        }

        if(closeModalBtn) closeModalBtn.addEventListener('click', closeLogoutModal);
        if(cancelModalBtn) cancelModalBtn.addEventListener('click', closeLogoutModal);
        
        // --- JavaScript for Delete Modal ---
        const deleteModal = document.getElementById('delete-modal');
        const deleteCancelBtn = document.getElementById('delete-cancel-btn');
        const deleteConfirmBtn = document.getElementById('delete-confirm-btn');
        
        function showDeleteModal(deleteUrl) {
            if (deleteModal) {
                deleteConfirmBtn.href = deleteUrl;
                deleteModal.style.display = 'flex';
            }
        }
        
        function closeDeleteModal() {
            if (deleteModal) deleteModal.style.display = 'none';
        }
        
        if(deleteCancelBtn) deleteCancelBtn.addEventListener('click', closeDeleteModal);
        
    </script>
    
</body>
</html>
<?php
}

// Function to define navigation links based on role
function get_nav_links($role) {
    $links = [];
    switch ($role) {
        
        case 'Admin':
            $links[] = ['name' => 'Dashboard', 'url' => 'admindashboard.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>'];
            $links[] = ['name' => 'Activity Log', 'url' => 'activity_log.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0l2.5-2.5M11.5 4L12 3m0 0l.5.5M12 3v.5M3 7l2.5 2.5m0 0l2.5-2.5M11 5.882l-7 3.5v3.676l7 3.5 7-3.5V9.382l-7-3.5z"></path>'];
            $links[] = ['name' => 'Reviews', 'url' => 'admin_reviews.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>'];
            $links[] = ['name' => 'Reports', 'url' => 'admin_reports.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>'];
            
            // *** NEW LINK ADDED HERE ***
            $links[] = ['name' => 'Send Request', 'url' => 'send_request.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>'];
            
            $links[] = ['name' => 'Create New User', 'url' => 'create_user.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20h-2m2 0h-2M15 4h3m-3 0V3m-2 4h4M4 8h10M4 12h10M4 16h10"></path>'];
            break;
            
        case 'Health Worker':
            $links[] = ['name' => 'Dashboard', 'url' => 'hwdashboard.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>'];
            $links[] = ['name' => 'List of Patients', 'url' => 'patient_list.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19.5c.39.05.79.05 1.18 0l7.5-1.14a1 1 0 00.82-1V5.64a1 1 0 00-.82-1L11.18 3.5c-.39-.05-.79-.05-1.18 0L2.5 4.64a1 1 0 00-.82 1v11.72a1 1 0 00.82 1l7.5 1.14zM12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM12 14c-2.21 0-4 1.79-4 4h8c0-2.21-1.79-4-4-4z"></path>'];
            $links[] = ['name' => 'Requests', 'url' => 'hw_requests.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>'];
            $links[] = ['name' => 'Reports', 'url' => 'hw_reports.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>'];
            break;
            
        case 'Patient':
            $links[] = ['name' => 'Dashboard', 'url' => 'patientdashboard.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>'];
            $links[] = ['name' => 'View My Record', 'url' => 'view_record.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>'];
            $links[] = ['name' => 'Follow-Up Consultation', 'url' => 'follow_up.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>'];
            $links[] = ['name' => 'Send Review', 'url' => 'send_review.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>'];
            break;
    }
    
    // Add Logout link for all logged-in roles
    $links[] = ['name' => 'Logout', 'url' => '#', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>'];
    
    return $links;
}
?>