<?php
// This line includes the authentication guard.
require_once 'authentication.php';

// (Admin-Only) Guard this page
generate_header('Reports', 'Admin');
?>

<!-- Static placeholder page for Reports -->
<div class="space-y-8">

    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold mb-6">Patient Reports (Placeholder)</h3>
        
        <p class="text-gray-700">A graph showing patient concerns by category (e.g., Cough, Fever, Check-up) would go here.</p>
        <!-- Placeholder for a chart -->
        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg h-64 flex items-center justify-center">
            <span class="text-gray-400 font-medium">Patient Concerns Chart (JS chart)</span>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold mb-6">Review Reports (Placeholder)</h3>
        
        <p class="text-gray-700">A graph showing review types (Feedback vs. Suggestion) would go here.</p>
        <!-- Placeholder for a chart -->
        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg h-64 flex items-center justify-center">
            <span class="text-gray-400 font-medium">Review Types Chart (JS chart)</span>
        </div>
    </div>

</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>