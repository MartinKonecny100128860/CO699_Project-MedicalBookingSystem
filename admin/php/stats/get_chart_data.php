<?php
// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$usersPerMonth = $conn->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM users GROUP BY month")->fetch_all(MYSQLI_ASSOC);
$appointments = $conn->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status")->fetch_all(MYSQLI_ASSOC);

echo json_encode(["usersPerMonth" => $usersPerMonth, "appointments" => $appointments]);
?>
