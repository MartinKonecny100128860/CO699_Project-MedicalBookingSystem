<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die(json_encode(["error" => "Unauthorized access"]));
}

// Check if appointment_id is provided
if (!isset($_POST['appointment_id'])) {
    die(json_encode(["error" => "No appointment ID provided"]));
}

$appointmentId = intval($_POST['appointment_id']);

// Database connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Delete appointment from the database
$stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointmentId);

if ($stmt->execute()) {
    echo json_encode(["success" => "Appointment completed successfully!"]);
} else {
    echo json_encode(["error" => "Failed to complete appointment"]);
}

$stmt->close();
$conn->close();
?>
