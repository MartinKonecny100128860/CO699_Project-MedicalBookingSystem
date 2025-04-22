<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false]);
    exit;
}

if (!isset($_POST['user_id'], $_POST['role'])) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = intval($_POST['user_id']);
$newRole = $_POST['role'];

$allowedRoles = ['admin', 'staff', 'doctor'];
if (!in_array($newRole, $allowedRoles)) {
    echo json_encode(['success' => false]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
if ($conn->connect_error) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
$stmt->bind_param("si", $newRole, $userId);

$success = $stmt->execute();

echo json_encode(['success' => $success]);

$stmt->close();
$conn->close();
?>
