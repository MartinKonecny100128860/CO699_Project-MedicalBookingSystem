<?php
session_start();

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = $_POST['report_id'] ?? null;
    $reason = $_POST['reason'] ?? '';

    if ($report_id && !empty($reason)) {
        $sql = "UPDATE medical_reports 
                SET referral_status = 'Pending', referral_reason = ?, referral_date = NOW(), referred_by = ?
                WHERE report_id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $reason, $_SESSION['user_id'], $report_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Referral successfully added!"]);
        } else {
            echo json_encode(["error" => "Failed to add referral."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Invalid data provided."]);
    }
}

$conn->close();
?>
