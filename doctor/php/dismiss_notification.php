<?php
session_start();

// Ensure the user is logged in (role doesn't matter here)
if (!isset($_SESSION['user_id'])) {
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

// Only allow deletion of the user's own notifications
$stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notifId, $userId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$conn->close();
?>
