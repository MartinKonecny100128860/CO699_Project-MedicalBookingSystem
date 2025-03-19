<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}

// Check if appointment ID is received
if (!isset($_POST["appointment_id"])) {
    die(json_encode(["error" => "No appointment ID provided."]));
}

$appointmentId = intval($_POST["appointment_id"]);

// Delete the appointment from the database
$stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointmentId);

if ($stmt->execute()) {
    echo json_encode(["success" => "Appointment successfully canceled."]);
} else {
    echo json_encode(["error" => "Failed to cancel appointment."]);
}

$stmt->close();
$conn->close();
?>
