<?php
session_start();

// Redirect if not logged in or not a patient
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'patient') {
    echo json_encode(["error" => "Unauthorized action."]);
    exit();
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}

// Ensure appointment_id is provided
if (!isset($_POST['appointment_id'])) {
    echo json_encode(["error" => "Missing appointment ID."]);
    exit();
}

$appointment_id = intval($_POST['appointment_id']);
$patient_id = $_SESSION['user_id'];

// Verify the appointment belongs to the logged-in patient
$sql = "SELECT * FROM appointments WHERE appointment_id = ? AND patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "Appointment not found or unauthorized access."]);
    exit();
}

// Delete the appointment
$deleteSQL = "DELETE FROM appointments WHERE appointment_id = ?";
$deleteStmt = $conn->prepare($deleteSQL);
$deleteStmt->bind_param("i", $appointment_id);

if ($deleteStmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to cancel appointment."]);
}

$deleteStmt->close();
$conn->close();
?>
