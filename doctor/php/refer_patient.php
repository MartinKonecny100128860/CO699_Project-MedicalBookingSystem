<?php
// Start session to access session variables
session_start();

// Create database connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

// Check if connection failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only handle the request if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the report ID and referral reason from the POST request
    $report_id = $_POST['report_id'] ?? null;
    $reason = $_POST['reason'] ?? '';

    // Proceed if report ID and reason are both valid
    if ($report_id && !empty($reason)) {
        // Prepare SQL to update the report with referral status and details
        $sql = "UPDATE medical_reports 
                SET referral_status = 'Pending', referral_reason = ?, referral_date = NOW(), referred_by = ?
                WHERE report_id = ?";

        // Bind parameters and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $reason, $_SESSION['user_id'], $report_id);

        // Respond with success or error message
        if ($stmt->execute()) {
            echo json_encode(["success" => "Referral successfully added!"]);
        } else {
            echo json_encode(["error" => "Failed to add referral."]);
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // If input data is invalid, return error message
        echo json_encode(["error" => "Invalid data provided."]);
    }
}

// Close the database connection
$conn->close();
?>
