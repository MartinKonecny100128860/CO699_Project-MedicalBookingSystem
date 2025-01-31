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
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $admin_id = $_SESSION['user_id']; // Get admin ID from session

    // Fetch admin's username
    $adminQuery = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    $adminQuery->bind_param("i", $admin_id);
    $adminQuery->execute();
    $adminQuery->bind_result($admin_username);
    $adminQuery->fetch();
    $adminQuery->close();

    // Build the update query
    $query = "UPDATE users SET username = ?, email = ?";
    $params = [$username, $email];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query .= ", password = ?";
        $params[] = $hashedPassword;
    }

    $query .= " WHERE user_id = ?";
    $params[] = $user_id;

    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);

    if ($stmt->execute()) {
        // Log the action
        $logAction = "Admin ID: $admin_id \"$admin_username\" has edited User's Details ID: $user_id";
        $logStmt = $conn->prepare("INSERT INTO logs (admin_id, action) VALUES (?, ?)");
        $logStmt->bind_param("is", $admin_id, $logAction);
        $logStmt->execute();

        $response['success'] = true;
        $response['message'] = 'User details updated successfully.';
    } else {
        $response['message'] = 'Error updating user details.';
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>