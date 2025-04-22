<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$uid = $_SESSION['user_id'];

$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $uid");
$conn->close();

echo json_encode(['success' => true]);
