<?php
session_start();

// Redirect if not logged in or not a doctor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'doctor') {
    die(json_encode(["error" => "Unauthorized access."]));
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed."]));
}

// Validate Form Data
if (!isset($_POST['patient_id'], $_POST['diagnosis'], $_POST['treatment'])) {
    die(json_encode(["error" => "Incomplete data."]));
}

$patient_id = $_POST['patient_id'];
$doctor_id = $_SESSION['user_id'];
$diagnosis = $conn->real_escape_string($_POST['diagnosis']);
$treatment = $conn->real_escape_string($_POST['treatment']);
$notes = $conn->real_escape_string($_POST['notes']);

// Insert medical report
$stmt = $conn->prepare("INSERT INTO medical_reports (patient_id, doctor_id, diagnosis, treatment, notes, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iisss", $patient_id, $doctor_id, $diagnosis, $treatment, $notes);

if ($stmt->execute()) {
    // 🔔 Notify patient
    $notifMsg = "A new medical report has been shared with you by your doctor.";
    $conn->query("INSERT INTO notifications (user_id, message) VALUES ($patient_id, '$notifMsg')");

    header("Location: ../viewappointments.php?success=Medical report saved");
    exit();
} else {
    die(json_encode(["error" => "Failed to save medical report."]));
}

$stmt->close();
$conn->close();
?>
