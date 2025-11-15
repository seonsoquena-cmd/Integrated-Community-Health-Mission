<?php
// This line includes the authentication guard.
require_once 'authentication.php';
require_once 'connection.php'; // We need the database

// (Health Worker-Only) Guard this page
generate_header('View/Edit Patient Record', 'Health Worker');

// --- START: PAGE LOGIC ---

$message = '';
$message_type = ''; // 'success' or 'error'
$patient_id = $_GET['id'] ?? 0;
$hw_id = $_SESSION['user_id']; // The logged-in Health Worker

// --- 1. HANDLE POST REQUESTS (UPDATE, ADD RECORD, or SCHEDULE FOLLOW-UP) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $action = $_POST['action'] ?? '';
    
    // --- ACTION A: UPDATE PATIENT PROFILE ---
    if ($action == 'update_profile' && $patient_id > 0) {
        $full_name = trim($_POST['full_name'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $purok = trim($_POST['purok'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');
        
        $sql = "UPDATE patients SET full_name = ?, dob = ?, address = ?, purok = ?, contact_number = ? WHERE patient_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssi", $full_name, $dob, $address, $purok, $contact, $patient_id);
            if ($stmt->execute()) {
                $message = "Patient profile updated successfully!";
                $message_type = 'success';
            } else {
                $message = "Error: Could not update profile. " . $conn->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
    
    // --- ACTION B: ADD NEW HEALTH RECORD ---
    if ($action == 'add_record' && $patient_id > 0) {
        // (Form fields...)
        $height_cm = trim($_POST['height_cm'] ?? '');
        $weight_kg = trim($_POST['weight_kg'] ?? '');
        $temp_c = trim($_POST['temperature_c'] ?? '');
        $bp = trim($_POST['blood_pressure'] ?? '');
        $complaint = trim($_POST['chief_complaint'] ?? '');
        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $record_date = date('Y-m-d'); // Use today's date
        
        if (empty($complaint) && empty($diagnosis)) {
            $message = "Error: Please enter at least a Chief Complaint or Diagnosis.";
            $message_type = 'error';
        } else {
            $sql = "INSERT INTO health_records (patient_id, hw_id, record_date, height_cm, weight_kg, temperature_c, blood_pressure, chief_complaint, diagnosis, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt = $conn->prepare($sql)) {
                $height_cm = $height_cm ?: null;
                $weight_kg = $weight_kg ?: null;
                $temp_c = $temp_c ?: null;
                $bp = $bp ?: null;

                $stmt->bind_param("iisddsssss", $patient_id, $hw_id, $record_date, $height_cm, $weight_kg, $temp_c, $bp, $complaint, $diagnosis, $notes);
                if ($stmt->execute()) {
                    $message = "New health record added successfully!";
                    $message_type = 'success';
                } else {
                    $message = "Error: Could not add record. " . $conn->error;
                    $message_type = 'error';
                }
                $stmt->close();
            }
        }
    }
    
    // --- *** NEW *** ACTION C: SCHEDULE FOLLOW-UP ---
    if ($action == 'schedule_followup' && $patient_id > 0) {
        $date_scheduled = trim($_POST['date_scheduled'] ?? '');
        $hw_comments = trim($_POST['hw_comments'] ?? '');
        
        if (empty($date_scheduled) || empty($hw_comments)) {
            $message = "Error: Please select a date and add comments for the follow-up.";
            $message_type = 'error';
        } else {
            $sql = "INSERT INTO follow_ups (patient_id, hw_id, date_scheduled, hw_comments, status) 
                    VALUES (?, ?, ?, ?, 'Pending')";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("isss", $patient_id, $hw_id, $date_scheduled, $hw_comments);
                if ($stmt->execute()) {
                    $message = "Follow-up scheduled successfully! The patient will be notified.";
                    $message_type = 'success';
                } else {
                    $message = "Error: Could not schedule follow-up. " . $conn->error;
                    $message_type = 'error';
                }
                $stmt->close();
            }
        }
    }
}


// --- 2. FETCH ALL DATA FOR THE PAGE (GET REQUEST) ---
$patient_info = null;
$health_records = [];
$follow_ups = []; // <-- NEW

if ($patient_id > 0) {
    // Fetch Patient Profile
    $sql_patient = "SELECT * FROM patients WHERE patient_id = ?";
    if ($stmt = $conn->prepare($sql_patient)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $patient_info = $result->fetch_assoc();
        }
        $stmt->close();
    }
    
    // Fetch Health Records
    $sql_records = "SELECT hr.*, u.full_name AS hw_name 
                    FROM health_records hr 
                    LEFT JOIN users u ON hr.hw_id = u.user_id 
                    WHERE hr.patient_id = ? 
                    ORDER BY hr.record_date DESC, hr.record_id DESC";
    if ($stmt = $conn->prepare($sql_records)) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $health_records[] = $row;
        }
        $stmt->close();
    }
    
    // --- *** NEW *** FETCH PAST FOLLOW-UPS ---
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
    
} else if (empty($message)) { // Only show this error if another error isn't already set
    $message = "Invalid Patient ID. Please go back to the list.";
    $message_type = 'error';
}

$conn->close();
// --- END: PAGE LOGIC ---
?>

<div class="space-y-8">
    
    <!-- Back link -->
    <a href="patient_list.php" class="inline-flex items-center text-blue-600 hover:text-blue-800">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to Patient List
    </a>
    
    <!-- Display Success/Error Messages -->
    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Show forms only if patient was found -->
    <?php if ($patient_info): ?>

    <!-- Patient Information (Editable) -->
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Patient Profile</h3>
        <form action="edit_patient_record.php?id=<?= $patient_info['patient_id'] ?>" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="update_profile">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($patient_info['full_name']) ?>">
                </div>
                <div>
                    <label for="dob" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($patient_info['dob']) ?>">
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <input type="text" id="address" name="address" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($patient_info['address']) ?>">
                </div>
                <div>
                    <label for="purok" class="block text-sm font-medium text-gray-700">Purok</label>
                    <input type="text" id="purok" name="purok" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($patient_info['purok']) ?>">
                </div>
                <div>
                    <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($patient_info['contact_number']) ?>">
                </div>
            </div>
            <div class="flex justify-end pt-4">
                <button type="submit" class="py-2 px-5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    Save Patient Info
                </button>
            </div>
        </form>
    </div>
    
    <!-- Add New Health Record Form -->
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Add New Health Record</h3>
        <form action="edit_patient_record.php?id=<?= $patient_info['patient_id'] ?>" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add_record">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label for="height_cm" class="block text-sm font-medium text-gray-700">Height (cm)</label>
                    <input type="number" step="0.1" id="height_cm" name="height_cm" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="weight_kg" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                    <input type="number" step="0.1" id="weight_kg" name="weight_kg" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="temperature_c" class="block text-sm font-medium text-gray-700">Temp (°C)</label>
                    <input type="number" step="0.1" id="temperature_c" name="temperature_c" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="blood_pressure" class="block text-sm font-medium text-gray-700">Blood Pressure</label>
                    <input type="text" id="blood_pressure" name="blood_pressure" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., 120/80">
                </div>
            </div>
            <div>
                <label for="chief_complaint" class="block text-sm font-medium text-gray-700">Chief Complaint</label>
                <textarea id="chief_complaint" name="chief_complaint" rows="2" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Patient's reported issues..."></textarea>
            </div>
            <div>
                <label for="diagnosis" class="block text-sm font-medium text-gray-700">Diagnosis / Findings</label>
                <textarea id="diagnosis" name="diagnosis" rows="2" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Health worker's findings..."></textarea>
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Prescription & Notes</label>
                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Prescriptions, advice, etc..."></textarea>
            </div>
            <div class="flex justify-end pt-4">
                <button type="submit" class="py-2 px-5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                    Add Record
                </button>
            </div>
        </form>
    </div>
    
    <!-- *** NEW: Schedule Follow-Up Form *** -->
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Schedule Follow-Up</h3>
        
        <form action="edit_patient_record.php?id=<?= $patient_info['patient_id'] ?>" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="schedule_followup">
            <div>
                <label for="date_scheduled" class="block text-sm font-medium text-gray-700">Follow-Up Date</label>
                <input type="date" id="date_scheduled" name="date_scheduled" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label for="hw_comments" class="block text-sm font-medium text-gray-700">Comments / Instructions for Patient</label>
                <textarea id="hw_comments" name="hw_comments" rows="4" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Please come back for a final check-up..." required></textarea>
            </div>
            <div class="flex justify-end pt-4">
                <button type="submit" class="py-2 px-5 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition">
                    Schedule Follow-Up
                </button>
            </div>
        </form>
    </div>

    <!-- *** NEW: Past Follow-Ups List *** -->
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Scheduled Follow-Ups</h3>
        <div class="space-y-6">
            <?php if (empty($follow_ups)): ?>
                <div class="text-center text-gray-500 p-4">
                    No follow-ups have been scheduled for this patient.
                </div>
            <?php endif; ?>
            
            <?php foreach($follow_ups as $follow_up): ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-lg font-semibold text-blue-700">Date: <?= htmlspecialchars($follow_up['date_scheduled']) ?></h4>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                        <?= $follow_up['status'] == 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                        <?= htmlspecialchars($follow_up['status']) ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mt-2"><span class="font-medium">HW Comments:</span> <?= htmlspecialchars($follow_up['hw_comments']) ?></p>
                    <span class="text-xs text-gray-400">Scheduled by: <?= htmlspecialchars($follow_up['hw_name'] ?? 'N/A') ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Past Health Records (Now Dynamic) -->
    <div class="bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Past Health Records</h3>
        <div class="space-y-6">
            <?php if (empty($health_records)): ?>
                <div class="text-center text-gray-500 p-4">
                    This patient has no past health records.
                </div>
            <?php endif; ?>
            
            <?php foreach($health_records as $record): ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-lg font-semibold text-blue-700">Visit Date: <?= htmlspecialchars($record['record_date']) ?></h4>
                    <span class="text-sm text-gray-500">Recorded by: <?= htmlspecialchars($record['hw_name'] ?? 'N/A') ?></span>
                </div>
                <div>
                    <p class="text-sm text-gray-700"><span class="font-medium">Diagnosis:</span> <?= htmlspecialchars($record['diagnosis']) ?></p>
                    <p class="text-sm text-gray-600 mt-2"><span class="font-medium">Notes:</span> <?= htmlspecialchars($record['notes']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php else: ?>
        <!-- This shows if the patient_id in the URL was invalid -->
        <div class="bg-white p-8 rounded-lg shadow text-center">
            <h3 class="text-xl font-semibold text-red-700">Patient Not Found</h3>
            <p class="text-gray-500 mt-2">The patient ID is missing or invalid. Please go back to the list and try again.</p>
        </div>
    <?php endif; ?>

</div>

<?php
// This line includes the footer and closes the HTML
generate_footer();
?>