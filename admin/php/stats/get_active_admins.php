<?php
    header("Content-Type: application/json");

    // Database connection setup
    $conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
    if ($conn->connect_error) {
        echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
        exit();
    }

    // Query to get admins who were active in the last 10 minutes
    $onlineAdminsQuery = "SELECT user_id, username 
                        FROM users 
                        WHERE last_active >= NOW() - INTERVAL 10 MINUTE 
                        AND role = 'admin'";

    $onlineAdminsResult = $conn->query($onlineAdminsQuery);

    if (!$onlineAdminsResult) {
        echo json_encode(["error" => "Error fetching active admins: " . $conn->error]);
        exit();
    }

    $admins = [];
    while ($admin = $onlineAdminsResult->fetch_assoc()) {
        $admins[] = $admin;
    }

    echo json_encode($admins);
?>
