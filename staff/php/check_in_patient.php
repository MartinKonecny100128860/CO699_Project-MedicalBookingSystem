<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];

    $conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
    $conn->set_charset("utf8mb4");

    $stmt = $conn->prepare("UPDATE appointments SET status = 'Arrived' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: ../staffappointments.php");
    exit();
}
?>
