<?php
session_start();

// Ensure the user is a logged-in doctor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorised']);
    exit();
}

// Validate POST data
if (!isset($_POST['case_id']) || !isset($_POST['action']) || $_POST['action'] !== 'take_case') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$case_id = (int)$_POST['case_id'];
$doctor_name = $_SESSION['username']; // or use first/last name if stored

// 📌 TODO: Update this part to interact with your real database
// For now, simulate success:
$response = [
    'status' => 'success',
    'message' => "Case #{$case_id} assigned to Dr. {$doctor_name}"
];

// Optional logging for debug/dev
// file_put_contents("logs/assignment.log", "Case $case_id taken by $doctor_name\n", FILE_APPEND);

header('Content-Type: application/json');
echo json_encode($response);
