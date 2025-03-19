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

// Fetch logs with pagination, filtering, and search
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $logsPerPage = isset($_GET["logs_per_page"]) ? intval($_GET["logs_per_page"]) : 5;
    $currentPage = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
    $offset = ($currentPage - 1) * $logsPerPage;

    // Filters
    $searchQuery = isset($_GET["search"]) ? "%" . $conn->real_escape_string($_GET["search"]) . "%" : "%";
    $adminIdFilter = isset($_GET["admin_id"]) && $_GET["admin_id"] !== "" ? intval($_GET["admin_id"]) : null;
    $actionFilter = isset($_GET["action"]) && $_GET["action"] !== "" ? "%" . $conn->real_escape_string($_GET["action"]) . "%" : "%";

    // Base query with filtering
    $logsQuery = "SELECT log_id, admin_id, action, 
                         DATE_FORMAT(log_timestamp, '%d/%m/%Y %H:%i:%s') AS timestamp 
                  FROM logs 
                  WHERE action LIKE ? AND (admin_id = ? OR ? IS NULL)
                  ORDER BY log_timestamp DESC 
                  LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($logsQuery);
    $stmt->bind_param("siiii", $actionFilter, $adminIdFilter, $adminIdFilter, $logsPerPage, $offset);
    $stmt->execute();
    $logsResult = $stmt->get_result();
    $logs = $logsResult->fetch_all(MYSQLI_ASSOC);

    // Get total logs count
    $countQuery = "SELECT COUNT(*) AS total FROM logs WHERE action LIKE ? AND (admin_id = ? OR ? IS NULL)";
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param("sii", $actionFilter, $adminIdFilter, $adminIdFilter);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $totalLogs = $countResult->fetch_assoc()["total"];

    echo json_encode([
        "logs" => $logs,
        "totalLogs" => $totalLogs,
        "hasMorePages" => ($offset + count($logs)) < $totalLogs
    ]);
    exit();
}

// Handle deleting a log entry
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

echo json_encode(["error" => "Invalid request."]);
exit();
