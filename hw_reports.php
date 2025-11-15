<?php
// This line includes the authentication guard.
require_once 'authentication.php';

// (Health Worker-Only) Guard this page
generate_header('Reports', 'Health Worker');
?>

<!-- Static placeholder page for Reports -->
<div class="space-y-8">

    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold mb-6">Patient Concerns Report (Placeholder)</h3>
        
        <p class="text-gray-700">This report will show a detailed breakdown of all diagnoses and chief complaints recorded during missions.</p>
        
        <!-- Placeholder for a chart -->
        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg h-80 flex items-center justify-center">
            <span class="text-gray-400 font-medium">Diagnoses Bar Chart (JS chart)</span>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold mb-6">Mission & Request Status (Placeholder)</h3>
        
        <p class="text-gray-700">This report will show the ratio of all approved vs. rejected mission requests.</p>
        
        <!-- Placeholder for a chart -->
        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg h-80 flex items-center justify-center">
            <span class="text-gray-400 font-medium">Request Status Pie Chart (JS chart)</span>
        </div>
    </div>

</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>