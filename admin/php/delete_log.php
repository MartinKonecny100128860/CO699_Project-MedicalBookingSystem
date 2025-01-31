<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["message" => "Unauthorized access."]);
    exit();
}

// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die(json_encode(["message" => "Database connection failed."]));
}

// Validate and delete log entry
if (isset($_POST['log_id'])) {
    $logId = intval($_POST['log_id']);
    $deleteQuery = "DELETE FROM logs WHERE log_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $logId);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Log deleted successfully."]);
    } else {
        echo json_encode(["message" => "Error deleting log."]);
    }

    $stmt->close();
} else {
    echo json_encode(["message" => "Invalid request."]);
}

$conn->close();
?>
