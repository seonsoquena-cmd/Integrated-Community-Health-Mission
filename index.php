<?php 
    // This MUST be the very first line of the file.
    session_start(); 
    
    // Get messages (if they exist) from the session
    $message = $_SESSION['message'] ?? '';
    $error_message = $_SESSION['error'] ?? '';
    unset($_SESSION['message']); // Clear messages
    unset($_SESSION['error']); // Clear errors
    
    // --- Get Debug Info ---
    $debug_pass_typed = $_SESSION['debug_password_typed'] ?? null;
    $debug_hash_from_db = $_SESSION['debug_hash_from_db'] ?? null;
    $debug_verify_result = $_SESSION['debug_verify_result'] ?? null;
    unset($_SESSION['debug_password_typed'], $_SESSION['debug_hash_from_db'], $_SESSION['debug_verify_result']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICHM Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="bg-white p-8 rounded-xl shadow-2xl border border-gray-100">
            <div class="flex flex-col items-center mb-6">
                <svg class="w-12 h-12 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <h2 class="text-3xl font-bold text-gray-800">Integrated Health Mission</h2>
                <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
            </div>

            <!-- Display Error Message -->
            <?php if (!empty($error_message)): ?>
                <div id="error-box" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm" role="alert">
                    <p class="font-bold">Login Failed</p>
                    <p class="text-xs"><?= htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Display Success Message (e.g., from signup) -->
            <?php if (!empty($message)): ?>
                <div id="success-box" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm" role="alert">
                    <p class="font-bold">Success</p>
                    <p class="text-xs"><?= htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- *** START: DEBUG INFO BOX *** -->
            <?php if (isset($debug_hash_from_db)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-lg mb-4 text-xs" style="word-wrap: break-word;">
                <p class="font-bold">Developer Debug Info:</p>
                <p><strong>Password Typed:</strong> (<?= strlen($debug_pass_typed) ?> chars) "<?= htmlspecialchars($debug_pass_typed) ?>"</p>
                <p><strong>Hash from DB:</strong> (<?= strlen($debug_hash_from_db) ?> chars) "<?= htmlspecialchars($debug_hash_from_db) ?>"</p>
                <p><strong>Verification Result:</strong> <?= $debug_verify_result ?></p>
            </div>
            <?php endif; ?>
            <!-- *** END: DEBUG INFO BOX *** -->

            <form action="login.php" method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter your username"
                           autocomplete="username">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter your password"
                           autocomplete="current-password">
                </div>
                
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Sign In
                </button>
                
                <div class="text-sm text-center text-gray-500">
                    Don't have an account yet? 
                    <a href="signup.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Create a new Account
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>