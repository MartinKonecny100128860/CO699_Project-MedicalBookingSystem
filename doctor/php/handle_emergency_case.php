<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'doctor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorised']);
    exit;
}

$doctorId = $_SESSION['user_id'];
$doctorName = $_SESSION['username']; // Make sure 'username' is stored during login
$caseId = $_POST['case_id'] ?? null;
$action = $_POST['action'] ?? '';

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

if ($action === 'take_case' && $caseId) {
    // Check if already taken
    $check = $conn->query("SELECT handled_by FROM emergency_cases WHERE id = $caseId");
    $existing = $check->fetch_assoc();

    if ($existing && $existing['handled_by'] !== null) {
        echo json_encode(['status' => 'error', 'message' => 'Case already taken']);
        $conn->close();
        exit;
    }

    // Assign the case to the doctor
    $conn->query("
        UPDATE emergency_cases 
        SET handled_by = $doctorId, status = 'In Progress', assigned_doctor = '$doctorName'
        WHERE id = $caseId
    ");

    echo json_encode(['status' => 'success']);
    $conn->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
$conn->close();
