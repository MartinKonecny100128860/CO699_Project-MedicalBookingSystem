<?php
// Start session to access session variables
session_start();

// Check if the user is logged in and is a staff member
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../new_login.php");
    exit(); // Stop script execution if not authorised
}

// Connect to the MySQL database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4"); // Support for special characters

// Collect form data from POST request
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password securely
$email = $_POST['email'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$house_no = $_POST['house_no'];
$street_name = $_POST['street_name'];
$post_code = $_POST['post_code'];
$city = $_POST['city'];
$telephone = $_POST['telephone'];
$emergency_contact = $_POST['emergency_contact'];
$gender = $_POST['gender'];
$date_of_birth = $_POST['date_of_birth'];
$role = 'patient'; // Role is hardcoded to 'patient'
$profile_picture = "assets/defaults/user_default.png"; // Default profile picture

// Handle uploaded profile picture
if (!empty($_FILES['profile_picture']['name'])) {
    $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION)); // Get file extension
    $allowed = ['jpg', 'jpeg', 'png', 'gif']; // Allowed file types

    if (in_array($ext, $allowed)) {
        $target_dir = "../assets/patient/"; // Directory to store profile pictures

        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Get the uploaded file name
        $filename = basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $filename;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = "assets/patient/" . $filename; // Update path for database
        }
    }
}

// Prepare SQL statement to insert patient data into `users` table
$stmt = $conn->prepare("INSERT INTO users 
    (username, password, role, email, first_name, last_name, house_no, street_name, post_code, city, telephone, emergency_contact, gender, profile_picture, date_of_birth) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Bind parameters to the SQL statement
$stmt->bind_param("sssssssssssssss", 
    $username, $password, $role, $email, $first_name, $last_name,
    $house_no, $street_name, $post_code, $city, $telephone,
    $emergency_contact, $gender, $profile_picture, $date_of_birth);

// Execute the SQL statement and show result
if ($stmt->execute()) {
    // Success message and redirect
    echo "<script>alert('Patient registered successfully.'); window.location.href = '../registerpatient.php';</script>";
} else {
    // Error message and redirect back
    echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
}

// Close the statement and database connection
$stmt->close();
$conn->close();
