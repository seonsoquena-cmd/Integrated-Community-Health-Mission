<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database connection

// (Health Worker-Only) Guard this page
generate_header('Dashboard', 'Health Worker');

// --- START: PAGE LOGIC ---

// 1. Get HW Info from Session
$hw_name = htmlspecialchars($_SESSION["full_name"]);
$hw_id_from_session = htmlspecialchars($_SESSION["user_id"]);
$hw_id_display = "HW-" . $hw_id_from_session;
$last_visit = date('F j, Y, g:i a');

// 2. Fetch stats from the database
$stats = [
    'pending_missions' => 0,
    'approved_missions' => 0,
    'pending_followups' => 0,
    'total_patients' => 0
];

// Get Pending MISSIONS (from Admin)
$sql_pending_missions = "SELECT COUNT(*) FROM hw_missions WHERE hw_id = ? AND status = 'Pending'";
if ($stmt = $conn->prepare($sql_pending_missions)) {
    $stmt->bind_param("i", $hw_id_from_session);
    $stmt->execute();
    $stmt->bind_result($stats['pending_missions']);
    $stmt->fetch();
    $stmt->close();
}

// Get Approved MISSIONS (from Admin)
$sql_approved_missions = "SELECT COUNT(*) FROM hw_missions WHERE hw_id = ? AND status = 'Approved'";
if ($stmt = $conn->prepare($sql_approved_missions)) {
    $stmt->bind_param("i", $hw_id_from_session);
    $stmt->execute();
    $stmt->bind_result($stats['approved_missions']);
    $stmt->fetch();
    $stmt->close();
}

// Get Pending FOLLOW-UPS (from Patients)
$sql_pending_followups = "SELECT COUNT(*) FROM follow_ups WHERE hw_id = ? AND status = 'Pending'";
if ($stmt = $conn->prepare($sql_pending_followups)) {
    $stmt->bind_param("i", $hw_id_from_session);
    $stmt->execute();
    $stmt->bind_result($stats['pending_followups']);
    $stmt->fetch();
    $stmt->close();
}

// Get Total Patients (from patient list)
// This is not specific to the HW, but total patients in the system.
// You could change this to "My Patients" if needed.
$sql_total_patients = "SELECT COUNT(*) FROM patients";
if ($result = $conn->query($sql_total_patients)) {
    $stats['total_patients'] = $result->fetch_row()[0];
    $result->free();
}

$conn->close();
// --- END: PAGE LOGIC ---
?>

<div class="space-y-8">

    <!-- Health Worker Info -->
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-4xl font-extrabold text-gray-800 mb-2">Welcome, <?= $hw_name ?>!</h3>
        <p class="text-lg text-gray-600">Here's a summary of your activities today.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 text-center">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-gray-500">Health Worker ID</p>
                <p class="text-lg font-bold text-gray-900"><?= $hw_id_display ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-gray-500">Last Visit (Logged in)</p>
                <p class="text-lg font-bold text-gray-900"><?= $last_visit ?></p>
            </div>
        </div>
    </div>

    <!-- Stats Grid (NOW DYNAMIC & RELEVANT) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="dashboard-card bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
            <p class="text-sm font-medium text-gray-500">Pending Mission Requests</p>
            <p class="text-4xl font-bold text-gray-900 mt-1"><?= $stats['pending_missions'] ?></p>
        </div>
        <div class="dashboard-card bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
            <p class="text-sm font-medium text-gray-500">Approved Missions</p>
            <p class="text-4xl font-bold text-gray-900 mt-1"><?= $stats['approved_missions'] ?></p>
        </div>
        <div class="dashboard-card bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
            <p class="text-sm font-medium text-gray-500">Pending Patient Follow-ups</p>
            <p class="text-4xl font-bold text-gray-900 mt-1"><?= $stats['pending_followups'] ?></p>
        </div>
        <div class="dashboard-card bg-white p-6 rounded-xl shadow-lg border-l-4 border-indigo-500">
            <p class="text-sm font-medium text-gray-500">Total Patients in System</p>
            <p class="text-4xl font-bold text-gray-900 mt-1"><?= $stats['total_patients'] ?></p>
        </div>
    </div>

    <!-- Graph Placeholder -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Activity Trends (Placeholder)</h3>
        <p class="text-gray-700">This area will show graphs of your assigned missions and patient follow-ups over time.</p>
        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg h-80 flex items-center justify-center">
            <span class="text-gray-400 font-medium">Activity Chart (JS chart)</span>
        </div>
    </div>

</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>