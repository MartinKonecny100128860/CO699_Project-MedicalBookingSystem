<?php
// Start session to access session variables
session_start();

// Set response header to JSON format
header('Content-Type: application/json');

// Ensure a doctor is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'doctor') {
    echo json_encode([]); // Return empty JSON if not authorized
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");

// If connection fails, return empty response
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}

// Sanitize input from POST request
$name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
$dob = isset($_POST['dob']) ? $conn->real_escape_string($_POST['dob']) : '';

// Build base SQL query to fetch patients
$sql = "SELECT user_id, first_name, last_name, dob, email FROM users WHERE role = 'patient'";

// Add conditions dynamically based on filters
if (!empty($name)) {
    $sql .= " AND (first_name LIKE '%$name%' OR last_name LIKE '%$name%')";
}

if (!empty($dob)) {
    $sql .= " AND dob = '$dob'";
}

// Execute the query
$result = $conn->query($sql);

// Prepare result array
$patients = [];

// If query returns rows, fetch each row as an associative array
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}

// Return the results as JSON
echo json_encode($patients);

// Close the database connection
$conn->close();
?>
