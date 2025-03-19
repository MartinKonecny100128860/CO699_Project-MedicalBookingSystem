<?php
session_start();

// Ensure request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request."]);
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

// Get POST values
$appointment_id = intval($_POST["appointment_id"]);
$doctor_id = intval($_POST["doctor_id"]);
$appointment_day = $conn->real_escape_string($_POST["appointment_day"]);
$appointment_time = $conn->real_escape_string($_POST["appointment_time"]);

// Ensure the selected time slot is not already booked
$checkQuery = "SELECT * FROM appointments WHERE doctor_id = ? AND appointment_day = ? AND appointment_time = ? AND appointment_id != ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("issi", $doctor_id, $appointment_day, $appointment_time, $appointment_id);
$stmt->execute();
$checkResult = $stmt->get_result();
$stmt->close();

if ($checkResult->num_rows > 0) {
    echo json_encode(["error" => "This time slot is already booked."]);
    exit();
}

// Update appointment
$updateQuery = "UPDATE appointments SET doctor_id = ?, appointment_day = ?, appointment_time = ? WHERE appointment_id = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("issi", $doctor_id, $appointment_day, $appointment_time, $appointment_id);
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Error updating appointment."]);
}

$stmt->close();
$conn->close();
?>
