<?php
// Start the session to access session variables
session_start();

// Check if the user is not logged in or is not a staff member
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    // Redirect unauthorised users to the login page
    header("Location: ../new_login.php");
    exit();
}

// Connect to the MySQL database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4"); // Set character encoding to handle special characters

// Check if the form was submitted via POST and the action is to cancel an appointment
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'cancel') {
    // Get the appointment ID from the form, ensuring it's an integer
    $appointment_id = intval($_POST['appointment_id']);

    // Prepare an SQL statement to update the appointment status to 'Cancelled'
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id); // Bind the appointment ID to the query
    $stmt->execute(); // Execute the query to update the record
}

// Close the database connection
$conn->close();

// Redirect back to the Manage Appointments page after the update
header("Location: ../manageappointments.php");
exit(); // Ensure no further code is executed
