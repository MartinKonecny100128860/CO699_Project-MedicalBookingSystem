<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['id'];
    $admin_id = $_SESSION['user_id']; // Get admin ID from session

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Log the action
        $logAction = "Deleted user (User ID: $user_id)";
        $logStmt = $conn->prepare("INSERT INTO logs (admin_id, action) VALUES (?, ?)");
        $logStmt->bind_param("is", $admin_id, $logAction);
        $logStmt->execute();

        $response['success'] = true;
        $response['message'] = 'User deleted successfully.';
    } else {
        $response['message'] = 'Error deleting user.';
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
