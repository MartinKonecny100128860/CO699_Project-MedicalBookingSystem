<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Database connection setup
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$response = ['success' => false, 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // Ensure the uploads directory exists
    $target_dir = "assets/admins/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Handle file upload
    if (!empty($_FILES['new_profile_picture']['name'])) {
        $new_profile_picture = $target_dir . basename($_FILES['new_profile_picture']['name']);

        if (move_uploaded_file($_FILES['new_profile_picture']['tmp_name'], $new_profile_picture)) {
            // Update the database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
            if (!$stmt) {
                $response['message'] = 'Database prepare failed: ' . $conn->error;
                echo json_encode($response);
                exit();
            }

            $stmt->bind_param("si", $new_profile_picture, $user_id);

            if ($stmt->execute()) {
                $_SESSION['profile_picture'] = $new_profile_picture;
                $response['success'] = true;
                $response['message'] = 'Profile picture updated successfully.';
            } else {
                $response['message'] = 'Database execution failed: ' . $stmt->error;
            }

            $stmt->close();
        } else {
            $response['message'] = 'File upload error: ' . $_FILES['new_profile_picture']['error'];
        }
    } else {
        $response['message'] = 'No file uploaded.';
    }
}

$conn->close();
echo json_encode($response);
