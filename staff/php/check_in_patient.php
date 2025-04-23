<?php
// Start session to manage authentication
session_start();

// Ensure the user is logged in and is a staff member
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../new_login.php"); // Redirect to login page if not authorised
    exit(); // Stop script execution
}

// Check if the request method is POST and appointment_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id']; // Get the appointment ID from POST request

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
    $conn->set_charset("utf8mb4"); // Set character encoding to support all characters

    // Prepare SQL statement to update the appointment status to 'Arrived'
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Arrived' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id); // Bind the appointment ID to the statement
    $stmt->execute(); // Execute the update query
    $stmt->close(); // Close the statement
    $conn->close(); // Close the database connection

    // Redirect back to the staff appointments page
    header("Location: ../staffappointments.php");
    exit(); // Ensure no further code is executed
}
?>
