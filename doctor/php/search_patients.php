<?php
session_start();

header('Content-Type: application/json');

// Ensure doctor is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'doctor') {
    echo json_encode([]);
    exit();
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}

$name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
$dob = isset($_POST['dob']) ? $conn->real_escape_string($_POST['dob']) : '';

// Build dynamic query
$sql = "SELECT user_id, first_name, last_name, dob, email FROM users WHERE role = 'patient'";

if (!empty($name)) {
    $sql .= " AND (first_name LIKE '%$name%' OR last_name LIKE '%$name%')";
}

if (!empty($dob)) {
    $sql .= " AND dob = '$dob'";
}

$result = $conn->query($sql);

$patients = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}

echo json_encode($patients);
$conn->close();
