<?php
header("Content-Type: application/json");

// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// ✅ Fetch users per month (group by year & month)
$usersPerMonthQuery = "SELECT YEAR(created_at) AS year, MONTH(created_at) AS month, COUNT(*) AS count 
                       FROM users GROUP BY year, month ORDER BY year DESC, month DESC";
$usersPerMonthResult = $conn->query($usersPerMonthQuery);
$usersPerMonth = $usersPerMonthResult->fetch_all(MYSQLI_ASSOC);

// ✅ Fetch appointment counts grouped by status
$appointmentsQuery = "SELECT status, COUNT(*) AS count FROM appointments GROUP BY status";
$appointmentsResult = $conn->query($appointmentsQuery);
$appointments = $appointmentsResult->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    "usersPerMonth" => $usersPerMonth, 
    "appointments" => $appointments
]);
?>
