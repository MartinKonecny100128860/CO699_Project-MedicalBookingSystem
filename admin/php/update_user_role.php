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

    // Check if admin is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit();
    }

    $response = ['success' => false, 'message' => ''];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];
        $admin_id = $_SESSION['user_id'];

        // Update role in database
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->bind_param("si", $role, $user_id);

        if ($stmt->execute()) {
            // Log the role update action
            $logAction = "Admin ID: $admin_id changed User ID: $user_id role to $role";
            $logStmt = $conn->prepare("INSERT INTO logs (admin_id, action) VALUES (?, ?)");
            $logStmt->bind_param("is", $admin_id, $logAction);
            $logStmt->execute();

            $response['success'] = true;
            $response['message'] = 'User role updated successfully.';
        } else {
            $response['message'] = 'Error updating role.';
        }
        $stmt->close();
    }

    $conn->close();
    echo json_encode($response);
?>
