<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
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
$role = 'patient';
$profile_picture = "assets/defaults/user_default.png";

// Handle profile picture
if (!empty($_FILES['profile_picture']['name'])) {
    $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($ext, $allowed)) {
        $target_dir = "../assets/patient/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $filename = basename($_FILES["profile_picture"]["name"]); //did it fix the prefix?
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = "assets/patient/" . $filename;
        }
    }
}

// Insert into users table
$stmt = $conn->prepare("INSERT INTO users 
    (username, password, role, email, first_name, last_name, house_no, street_name, post_code, city, telephone, emergency_contact, gender, profile_picture, date_of_birth) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssssssssss", 
    $username, $password, $role, $email, $first_name, $last_name, $house_no, $street_name, $post_code, $city, $telephone, $emergency_contact, $gender, $profile_picture, $date_of_birth);

if ($stmt->execute()) {
    echo "<script>alert('Patient registered successfully.'); window.location.href = '../registerpatient.php';</script>";
} else {
    echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
