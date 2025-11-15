<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database

// (Patient-Only) Guard this page
generate_header('Dashboard', 'Patient');

// --- START: PAGE LOGIC ---

// 1. Get Info from Session
$patient_name = htmlspecialchars($_SESSION["full_name"]);
$user_id = $_SESSION["user_id"];

// 2. Initialize variables
$patient_id_display = "P-{$user_id}"; // Default
$purok = "N/A";
$last_visit = "First Login";

// 3. Fetch Patient-Specific Info (Patient ID and Purok)
$sql_patient = "SELECT patient_id, purok FROM patients WHERE user_id = ?";
if ($stmt = $conn->prepare($sql_patient)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($patient_data = $result->fetch_assoc()) {
        $patient_id_display = "P-" . htmlspecialchars($patient_data['patient_id']);
        $purok = htmlspecialchars($patient_data['purok']);
    }
    $stmt->close();
}

// 4. Fetch Last Visit (Previous Login Time)
// We use 'OFFSET 1' to skip the login they *just* performed
$sql_visit = "SELECT timestamp FROM activity_log 
              WHERE user_id = ? AND activity_type = 'Login' 
              ORDER BY timestamp DESC 
              LIMIT 1 OFFSET 1";
if ($stmt = $conn->prepare($sql_visit)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($visit_data = $result->fetch_assoc()) {
        $last_visit = date('F j, Y, g:i a', strtotime($visit_data['timestamp']));
    }
    $stmt->close();
}
$conn->close();
// --- END: PAGE LOGIC ---
?>

<!-- This is the start of your page's HTML -->
<div class="space-y-8">

    <!-- Welcome Header -->
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-4xl font-extrabold text-gray-800 mb-2">Welcome back, <?= $patient_name ?>!</h3>
        <p class="text-lg text-gray-600">Here is your health summary at a glance.</p>
    </div>

    <!-- Patient Information Grid (NOW DYNAMIC) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Patient ID Card -->
        <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="flex-shrink-0 p-3 bg-blue-100 rounded-full">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Patient ID</p>
                <p class="text-xl font-bold text-gray-900"><?= $patient_id_display ?></p>
            </div>
        </div>
        
        <!-- Location (Purok) Card -->
        <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="flex-shrink-0 p-3 bg-green-100 rounded-full">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Location</p>
                <p class="text-xl font-bold text-gray-900"><?= $purok ?></p>
            </div>
        </div>
        
        <!-- Last Visit (Logged In) Card -->
        <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="flex-shrink-0 p-3 bg-yellow-100 rounded-full">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Last Visit</p>
                <p class="text-xl font-bold text-gray-900"><?= $last_visit ?></p>
            </div>
        </div>
        
    </div>
    
    <!-- Quick Action Links (FIXED) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="view_record.php" class="dashboard-card bg-white p-6 rounded-xl shadow-lg border-t-4 border-blue-500">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">View My Record</h3>
            <p class="text-gray-600">See your complete medical history, diagnoses, and vitals.</p>
        </a>
        <a href="follow_up.php" class="dashboard-card bg-white p-6 rounded-xl shadow-lg border-t-4 border-yellow-500">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Follow-Up Consultation</h3>
            <p class="text-gray-600">Check your schedule and see comments from your health worker.</p>
        </a>
        <a href="send_review.php" class="dashboard-card bg-white p-6 rounded-xl shadow-lg border-t-4 border-green-500">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Send Review</h3>
            <p class="text-gray-600">Send feedback or suggestions about our services.</p>
        </a>
    </div>

</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>