<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in by verifying if 'user_id' is set in session
if (!isset($_SESSION['user_id'])) {
    // If not logged in, return a JSON response indicating failure
    echo json_encode(["success" => false]);
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");

// Mark all notifications as read for the logged-in user
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = " . intval($_SESSION['user_id']));

// Return a JSON response indicating success
echo json_encode(["success" => true]);
?>

