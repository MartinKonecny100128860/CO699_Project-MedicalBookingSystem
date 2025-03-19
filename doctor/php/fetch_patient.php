<?php
session_start();
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Ensure POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['patient_id'])) {
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

$patient_id = intval($_POST['patient_id']); // Sanitize input

// Fetch patient details
$sql = "SELECT first_name, last_name, date_of_birth, email, telephone, house_no, street_name, post_code, city, gender FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if ($patient) {
    echo json_encode($patient);
} else {
    echo json_encode(["error" => "Patient not found"]);
}

$stmt->close();
$conn->close();
?>
