<?php
// Start session to access session variables
session_start();

// Redirect to login page if user is not logged in or not a staff member
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../new_login.php");
    exit();
}

// Connect to the MySQL database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4"); // Ensure correct character encoding

// Check if the request is a POST request (i.e. form submission)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize form input data
    $user_id = intval($_POST['user_id']);
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $house_no = $_POST['house_no'] ?? '';
    $street_name = $_POST['street_name'] ?? '';
    $post_code = $_POST['post_code'] ?? '';
    $city = $_POST['city'] ?? '';
    $emergency_contact = $_POST['emergency_contact'] ?? '';

    // Prepare an SQL statement to update the patient details in the database
    $stmt = $conn->prepare("UPDATE users SET 
        first_name = ?, last_name = ?, email = ?, telephone = ?, 
        house_no = ?, street_name = ?, post_code = ?, city = ?, emergency_contact = ? 
        WHERE user_id = ? AND role = 'patient'");

    // Bind the variables to the prepared statement
    $stmt->bind_param("sssssssssi", 
        $first_name, $last_name, $email, $telephone,
        $house_no, $street_name, $post_code, $city, $emergency_contact,
        $user_id
    );

    // Execute the statement and set session message accordingly
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Patient updated successfully.";
    } else {
        $_SESSION['error_msg'] = "Error updating patient: " . $conn->error;
    }

    // Close the prepared statement
    $stmt->close();
}

// Close the database connection
$conn->close();

// Redirect back to the viewpatients page with success/error message
header("Location: ../viewpatients.php");
exit();
