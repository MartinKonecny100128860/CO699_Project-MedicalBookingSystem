<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false]);
    exit();
}
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = " . intval($_SESSION['user_id']));
echo json_encode(["success" => true]);
?>
