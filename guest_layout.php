<?php
// This layout does NOT start a session or check for login,
// as it's for public-facing pages.

// Global function to generate the public page header
function generate_public_header($page_title) {
    $nav_links = [
        ['name' => 'Announcements', 'url' => 'guestdashboard.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882l-7 3.5v3.676l7 3.5 7-3.5V9.382l-7-3.5zM12 20a8 8 0 100-16 8 8 0 000 16z"></path>'],
        ['name' => 'Archives', 'url' => 'archives.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>'],
        ['name' => 'Login / Signup', 'url' => 'index.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>']
    ];
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
    <!-- Public Sidebar -->
    <aside class="sidebar w-64 h-screen bg-gray-800 text-white shadow-xl flex-shrink-0 hidden md:block">
        <div class="p-6">
            <h1 class="text-3xl font-extrabold tracking-tight">ICHM</h1>
            <p class="text-sm opacity-75 mt-1">Guest Portal</p>
        </div>
        <nav class="mt-8 space-y-2 px-4">
            <?php foreach ($nav_links as $link): ?>
                <a href="<?= $link['url'] ?>" class="flex items-center p-3 rounded-lg text-sm font-medium 
                   <?= $link['name'] == 'Login / Signup' ? 'text-gray-200 bg-blue-600 hover:bg-blue-700' : 'hover:bg-gray-700' ?> 
                   transition duration-150 <?= $page_title == $link['name'] ? 'bg-gray-900 shadow-md' : '' ?>">
                    
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
            <h2 class="text-4xl font-extrabold text-gray-800"><?= $page_title ?></h2>
        </div>

        <!-- Start of Page Specific Content -->
        <div class="p-6 md:p-8">
<?php
}

// Function to close the main content wrapper
function generate_public_footer() {
?>
        </div>
    </main>
</body>
</html>
<?php
}
?>