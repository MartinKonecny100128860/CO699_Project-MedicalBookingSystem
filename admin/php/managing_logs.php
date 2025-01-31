<?php
session_start();
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

// Fetch logs with pagination
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $logsPerPage = isset($_GET["logs_per_page"]) ? intval($_GET["logs_per_page"]) : 5;
    $currentPage = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
    $offset = ($currentPage - 1) * $logsPerPage;

    $logsQuery = "SELECT log_id, admin_id, action, DATE_FORMAT(timestamp, '%d/%m/%Y %H:%i:%s') AS timestamp 
                  FROM logs ORDER BY timestamp DESC LIMIT $logsPerPage OFFSET $offset";
    
    $logsResult = $conn->query($logsQuery);
    $logs = $logsResult->fetch_all(MYSQLI_ASSOC);

    echo json_encode(["logs" => $logs, "hasMorePages" => count($logs) === $logsPerPage]);
    exit();
}

// Handle log deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "delete") {
    if (!isset($_POST["log_id"]) || empty($_POST["log_id"])) {
        echo json_encode(["error" => "Invalid log ID."]);
        exit();
    }

    $logId = intval($_POST["log_id"]);
    $deleteQuery = "DELETE FROM logs WHERE log_id = ?";
    $stmt = $conn->prepare($deleteQuery);

    if (!$stmt) {
        echo json_encode(["error" => "Query preparation failed: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $logId);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Log deleted successfully."]);
    } else {
        echo json_encode(["error" => "Error deleting log."]);
    }

    $stmt->close();
    exit();
}

// If no valid request
echo json_encode(["error" => "Invalid request."]);
exit();
?>
