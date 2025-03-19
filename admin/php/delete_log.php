<?php
session_start();
header("Content-Type: application/json");

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["error" => "Unauthorized access."]);
    exit();
}

// Database connection
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "medicalbookingsystem";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Validate and delete log entry
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['log_id'])) {
    $logId = intval($_POST['log_id']);
    $deleteQuery = "DELETE FROM logs WHERE log_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $logId);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Log deleted successfully."]);
    } else {
        echo json_encode(["error" => "Error deleting log."]);
    }

    $stmt->close();
    exit();
}

echo json_encode(["error" => "Invalid request."]);
exit();
