<?php
    session_start();

    // Redirect to login page if not logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit();
    }

    // Database connection setup (Ensure correct DB name and charset)
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbName = "medicalbookingsystem"; // Corrected database name

    // Create database connection
    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
    $conn->set_charset("utf8mb4");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the admin is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit();
    }

    $response = ['success' => false, 'message' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['id'];
        $admin_id = $_SESSION['user_id'];

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
