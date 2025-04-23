<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(['success' => false]);
    exit();
}

$notifId = intval($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

if ($conn->connect_error || $notifId === 0) {
    echo json_encode(['success' => false]);
    exit();
}

$conn->query("DELETE FROM notifications WHERE id = $notifId AND user_id = $userId");

echo json_encode(['success' => true]);
$conn->close();
?>
