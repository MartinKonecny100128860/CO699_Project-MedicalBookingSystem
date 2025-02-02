<?php
    header("Content-Type: application/json");

    // Database connection setup
    $conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
    if ($conn->connect_error) {
        echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
        exit();
    }

    $logsQuery = "SELECT admin_id, action, DATE_FORMAT(timestamp, '%d/%m/%Y %H:%i:%s') AS timestamp 
                FROM logs ORDER BY timestamp DESC LIMIT 5";

    $logsResult = $conn->query($logsQuery);

    if (!$logsResult) {
        echo json_encode(["error" => "Error fetching logs: " . $conn->error]);
        exit();
    }

    $logs = [];
    while ($log = $logsResult->fetch_assoc()) {
        $logs[] = $log;
    }

    echo json_encode($logs);
?>
