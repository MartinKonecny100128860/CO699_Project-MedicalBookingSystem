<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $test_type = $_POST['test_type'];
    $test_summary = $_POST['test_summary'];
    $test_diagnosis = $_POST['test_diagnosis'];

    // Image Upload Handling
    $uploadDir = "../../uploads/tests/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $filePath = NULL; // Default to NULL if no file is uploaded
    if (!empty($_FILES["test_file"]["name"])) {
        $fileName = time() . "_" . basename($_FILES["test_file"]["name"]); // Unique filename
        $filePath = "uploads/tests/" . $fileName; // Relative path for DB storage
        $targetFilePath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES["test_file"]["tmp_name"], $targetFilePath)) {
            die("Error uploading file.");
        }
    }

    // Insert test record
    $stmt = $conn->prepare("INSERT INTO patient_tests (patient_id, doctor_id, test_type, test_summary, test_diagnosis, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $patient_id, $doctor_id, $test_type, $test_summary, $test_diagnosis, $filePath);

    if ($stmt->execute()) {
        // 🔔 Insert Notification for Patient
        $notifMsg = "A new test result has been shared with you by your doctor.";
        $conn->query("INSERT INTO notifications (user_id, message) VALUES ($patient_id, '$notifMsg')");

        echo "<script>alert('Test result added successfully!'); window.location.href='../createtest.php';</script>";
    } else {
        echo "<script>alert('Error adding test result.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
