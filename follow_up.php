<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database

// (Patient-Only) Guard this page
generate_header('Follow-Up Consultation', 'Patient');

// --- START: PAGE LOGIC ---

$user_id = $_SESSION['user_id'];
$patient_id = null;
$follow_ups = [];

// 1. Find the patient_id from the user_id
$sql_patient = "SELECT patient_id FROM patients WHERE user_id = ?";
if ($stmt_patient = $conn->prepare($sql_patient)) {
    $stmt_patient->bind_param("i", $user_id);
    if ($stmt_patient->execute()) {
        $result_patient = $stmt_patient->get_result();
        if ($result_patient->num_rows == 1) {
            $patient_id = $result_patient->fetch_assoc()['patient_id'];
        }
    }
    $stmt_patient->close();
}

// 2. Fetch all follow-ups for this patient
if ($patient_id) {
    $sql_followups = "SELECT f.*, u.full_name AS hw_name
                      FROM follow_ups f
                      LEFT JOIN users u ON f.hw_id = u.user_id
                      WHERE f.patient_id = ?
                      ORDER BY f.date_scheduled DESC";
    
    if ($stmt = $conn->prepare($sql_followups)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $follow_ups[] = $row;
        }
        $stmt->close();
    }
}
$conn->close();

// Helper function for status color
function getStatusClass($status) {
    switch ($status) {
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'Approved':
        case 'Done':
            return 'bg-green-100 text-green-800';
        case 'Rejected':
        case 'Missed':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
// --- END: PAGE LOGIC ---
?>

<div class="space-y-8">

    <?php if (empty($follow_ups)): ?>
        <div class="bg-white p-8 rounded-lg shadow text-center">
            <h3 class="text-xl font-semibold text-gray-700">No Follow-Ups</h3>
            <p class="text-gray-500 mt-2">You do not have any pending follow-up consultations scheduled.</p>
        </div>
    <?php endif; ?>

    <!-- Loop through DYNAMIC follow-ups -->
    <?php foreach ($follow_ups as $follow_up): ?>
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Follow-Up Scheduled</h3>
                <p class="text-lg text-gray-700">Date: <span class="font-bold text-blue-600"><?= htmlspecialchars($follow_up['date_scheduled']) ?></span></p>
            </div>
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?= getStatusClass($follow_up['status']) ?>">
                <?= htmlspecialchars($follow_up['status']) ?>
            </span>
        </div>
        
        <div class="mt-6 border-t pt-6">
            <label class="text-sm font-medium text-gray-500">Health Worker Comments</label>
            <p class="text-md text-gray-800 p-4 bg-gray-50 rounded-lg mt-2">
                <?= htmlspecialchars($follow_up['hw_comments']) ?>
            </p>
            <p class="text-xs text-gray-400 mt-2">Comment by: <?= htmlspecialchars($follow_up['hw_name'] ?? 'N/A') ?></p>
        </div>
    </div>
    <?php endforeach; ?>
    
    <div class="bg-white p-8 rounded-xl shadow-lg">
         <h3 class="text-2xl font-bold text-gray-800 mb-4">Request a New Follow-Up</h3>
         <p class="text-gray-600 mb-4">If you have a new concern or need to schedule an appointment, you can submit a request.</p>
         <button class="py-2 px-5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition opacity-50 cursor-not-allowed" disabled>
            Request New Consultation (Coming Soon)
         </button>
    </div>

</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>