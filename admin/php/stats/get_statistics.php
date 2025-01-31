<?php

// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header("Content-Type: application/json");

$response = [];

try {
    // Fetch total users
    $totalUsersQuery = "SELECT COUNT(*) AS count FROM users";
    $totalUsersResult = $conn->query($totalUsersQuery);
    if (!$totalUsersResult) throw new Exception("Error fetching total users: " . $conn->error);
    $response["totalUsers"] = $totalUsersResult->fetch_assoc()["count"];

    // Fetch total appointments
    $totalAppointmentsQuery = "SELECT COUNT(*) AS count FROM appointments";
    $totalAppointmentsResult = $conn->query($totalAppointmentsQuery);
    if (!$totalAppointmentsResult) throw new Exception("Error fetching total appointments: " . $conn->error);
    $response["totalAppointments"] = $totalAppointmentsResult->fetch_assoc()["count"];

    // Fetch total logs
    $totalLogsQuery = "SELECT COUNT(*) AS count FROM logs";
    $totalLogsResult = $conn->query($totalLogsQuery);
    if (!$totalLogsResult) throw new Exception("Error fetching total logs: " . $conn->error);
    $response["totalLogs"] = $totalLogsResult->fetch_assoc()["count"];

    // Fetch most active admin
    $mostActiveAdminQuery = "SELECT admin_id, COUNT(*) as activity FROM logs GROUP BY admin_id ORDER BY activity DESC LIMIT 1";
    $mostActiveAdminResult = $conn->query($mostActiveAdminQuery);
    if (!$mostActiveAdminResult) throw new Exception("Error fetching most active admin: " . $conn->error);
    $mostActiveAdminRow = $mostActiveAdminResult->fetch_assoc();
    $response["mostActiveAdmin"] = $mostActiveAdminRow ? $mostActiveAdminRow["admin_id"] : "-";

} catch (Exception $e) {
    $response["error"] = $e->getMessage();
}

echo json_encode($response);
?>

