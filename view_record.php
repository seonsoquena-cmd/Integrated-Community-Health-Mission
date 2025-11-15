<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database

// (Patient-Only) Guard this page
generate_header('View My Record', 'Patient');

// --- START: PAGE LOGIC ---

$user_id = $_SESSION['user_id'];
$patient_id = null; // We need to find the patient_id from the user_id

// 1. Fetch Patient's Profile Info
$patient_info = null;
$sql_patient = "SELECT patient_id, full_name, dob, address, purok, contact_number 
                FROM patients 
                WHERE user_id = ?";
if ($stmt = $conn->prepare($sql_patient)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $patient_info = $result->fetch_assoc();
        $patient_id = $patient_info['patient_id']; // Get the patient_id for the next query
    }
    $stmt->close();
}

// 2. Fetch Patient's Health Records
$health_records = [];
if ($patient_id) { // Only run this if we found a patient
    $sql_records = "SELECT 
                        hr.*, 
                        u.full_name AS hw_name 
                    FROM 
                        health_records hr
                    JOIN 
                        users u ON hr.hw_id = u.user_id
                    WHERE 
                        hr.patient_id = ?
                    ORDER BY 
                        hr.record_date DESC";
    
    if ($stmt = $conn->prepare($sql_records)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $health_records[] = $row;
        }
        $stmt->close();
    }
}
$conn->close();
// --- END: PAGE LOGIC ---
?>

<div class="space-y-8">

    <!-- Patient Information (Read-Only) -->
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">My Patient Information</h3>
        <p class="text-gray-600 mb-6">This information can only be updated by a Health Worker.</p>
        
        <?php if ($patient_info): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                <div class="border-b py-2">
                    <label class="text-sm font-medium text-gray-500">Full Name</label>
                    <p class="text-lg text-gray-900"><?= htmlspecialchars($patient_info['full_name']) ?></p>
                </div>
                <div class="border-b py-2">
                    <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                    <p class="text-lg text-gray-900"><?= htmlspecialchars($patient_info['dob'] ?? 'N/A') ?></p>
                </div>
                <div class="border-b py-2">
                    <label class="text-sm font-medium text-gray-500">Address</label>
                    <p class="text-lg text-gray-900"><?= htmlspecialchars($patient_info['address'] ?? 'N/A') ?></p>
                </div>
                <div class="border-b py-2">
                    <label class="text-sm font-medium text-gray-500">Purok</label>
                    <p class="text-lg text-gray-900"><?= htmlspecialchars($patient_info['purok'] ?? 'N/A') ?></p>
                </div>
                <div class="border-b py-2">
                    <label class="text-sm font-medium text-gray-500">Contact Number</label>
                    <p class="text-lg text-gray-900"><?= htmlspecialchars($patient_info['contact_number'] ?? 'N/A') ?></p>
                </div>
            </div>
        <?php else: ?>
            <p class="text-red-500">Could not find your patient profile. Please contact a health worker.</p>
        <?php endif; ?>
    </div>
    
    <!-- Health Records (Read-Only) -->
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">My Health Records</h3>
        
        <div class="space-y-6">
            
            <?php if (empty($health_records)): ?>
                <div class="text-center text-gray-500 p-4">
                    You have no health records on file.
                </div>
            <?php endif; ?>
            
            <!-- Loop through DYNAMIC records -->
            <?php foreach($health_records as $record): ?>
            <div class="border border-gray-200 rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-xl font-semibold text-blue-700">Visit Date: <?= htmlspecialchars($record['record_date']) ?></h4>
                    <span class="text-sm text-gray-500">Recorded by: <?= htmlspecialchars($record['hw_name']) ?></span>
                </div>
                
                <!-- Vitals -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <label class="text-xs font-medium text-gray-500">Height</label>
                        <p class="text-md font-bold text-gray-900"><?= htmlspecialchars($record['height_cm'] ?? 'N/A') ?> cm</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <label class="text-xs font-medium text-gray-500">Weight</label>
                        <p class="text-md font-bold text-gray-900"><?= htmlspecialchars($record['weight_kg'] ?? 'N/A') ?> kg</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <label class="text-xs font-medium text-gray-500">Temperature</label>
                        <p class="text-md font-bold text-gray-900"><?= htmlspecialchars($record['temperature_c'] ?? 'N/A') ?> °C</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <label class="text-xs font-medium text-gray-500">Blood Pressure</label>
                        <p class="text-md font-bold text-gray-900"><?= htmlspecialchars($record['blood_pressure'] ?? 'N/A') ?></p>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Chief Complaint</label>
                        <p class="text-md text-gray-800 p-3 bg-gray-50 rounded-lg"><?= htmlspecialchars($record['chief_complaint'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Diagnosis / Findings</label>
                        <p class="text-md text-gray-800 p-3 bg-gray-50 rounded-lg"><?= htmlspecialchars($record['diagnosis'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Health Worker Notes</label>
                        <p class="text-md text-gray-800 p-3 bg-gray-50 rounded-lg"><?= htmlspecialchars($record['notes'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>