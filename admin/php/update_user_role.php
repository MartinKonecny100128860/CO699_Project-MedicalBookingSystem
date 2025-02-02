<?php
    session_start();

    // Check if admin is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit();
    }

    // Database connection setup
    $conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
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
