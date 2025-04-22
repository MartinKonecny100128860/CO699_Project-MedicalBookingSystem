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
if (!isset($_POST['patient_id'], $_POST['medication'], $_POST['dosage'], $_POST['instructions'])) {
    die(json_encode(["error" => "Incomplete data."]));
}

$patient_id = $_POST['patient_id'];
$doctor_id = $_SESSION['user_id'];
$medication = $conn->real_escape_string($_POST['medication']);
$dosage = $conn->real_escape_string($_POST['dosage']);
$instructions = $conn->real_escape_string($_POST['instructions']);

// Insert into database
$stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, doctor_id, medication, dosage, instructions, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iisss", $patient_id, $doctor_id, $medication, $dosage, $instructions);

if ($stmt->execute()) {
    // Insert notification BEFORE redirect
    $notifMsg = "Your doctor has prescribed new medication. Check your prescriptions.";
    $conn->query("INSERT INTO notifications (user_id, message) VALUES ($patient_id, '$notifMsg')");

    // THEN redirect
    header("Location: ../viewappointments.php?success=Prescription saved");
    exit();
} else {
    die(json_encode(["error" => "Failed to save prescription."]));
}

// After successful prescription insertion
$patientId = $_POST['patient_id']; // or however you're getting the patient ID
$notifMsg = "Your doctor has prescribed new medication. Check your prescriptions.";
$conn->query("INSERT INTO notifications (user_id, message) VALUES ($patientId, '$notifMsg')");

$stmt->close();
?>
