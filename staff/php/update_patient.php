<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    $stmt = $conn->prepare("UPDATE users SET 
        first_name = ?, last_name = ?, email = ?, telephone = ?, 
        house_no = ?, street_name = ?, post_code = ?, city = ?, emergency_contact = ? 
        WHERE user_id = ? AND role = 'patient'");

    $stmt->bind_param("sssssssssi", 
        $first_name, $last_name, $email, $telephone,
        $house_no, $street_name, $post_code, $city, $emergency_contact,
        $user_id
    );

    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Patient updated successfully.";
    } else {
        $_SESSION['error_msg'] = "Error updating patient: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
header("Location: ../viewpatients.php");
exit();
