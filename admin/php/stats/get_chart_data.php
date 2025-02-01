<?php
header("Content-Type: application/json");

// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// ✅ Count total users (as an alternative to `created_at`)
$usersQuery = "SELECT COUNT(*) AS total FROM users";
$usersResult = $conn->query($usersQuery);
$usersData = $usersResult->fetch_assoc();

// ✅ Count appointments by status
$appointmentsQuery = "SELECT status, COUNT(*) AS count FROM appointments GROUP BY status";
$appointmentsResult = $conn->query($appointmentsQuery);
$appointments = $appointmentsResult->fetch_all(MYSQLI_ASSOC);

// ✅ Simulate user growth per month (since `created_at` is missing)
$fakeUserGrowth = [
    ["month" => "Jan", "count" => rand(5, 15)],
    ["month" => "Feb", "count" => rand(5, 15)],
    ["month" => "Mar", "count" => rand(5, 15)],
    ["month" => "Apr", "count" => rand(5, 15)],
    ["month" => "May", "count" => rand(5, 15)],
    ["month" => "Jun", "count" => rand(5, 15)],
    ["month" => "Jul", "count" => rand(5, 15)],
    ["month" => "Aug", "count" => rand(5, 15)],
    ["month" => "Sep", "count" => rand(5, 15)],
    ["month" => "Oct", "count" => rand(5, 15)],
    ["month" => "Nov", "count" => rand(5, 15)],
    ["month" => "Dec", "count" => rand(5, 15)]
];

echo json_encode([
    "usersPerMonth" => $fakeUserGrowth, 
    "totalUsers" => $usersData['total'],
    "appointments" => $appointments
]);
?>
