<?php
// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$logsQuery = "SELECT admin_id, action, DATE_FORMAT(timestamp, '%d/%m/%Y %H:%i:%s') AS timestamp 
              FROM logs ORDER BY timestamp DESC LIMIT 5";

$logsResult = $conn->query($logsQuery);
$logs = [];

while ($log = $logsResult->fetch_assoc()) {
    $logs[] = $log;
}

echo json_encode($logs);
?>
