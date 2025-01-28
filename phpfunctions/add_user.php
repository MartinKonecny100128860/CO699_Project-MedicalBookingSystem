<?php
session_start();

// Check if the admin is logged in
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
    // Sanitize and collect data
    $first_name = htmlspecialchars($_POST['first_name'], ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars($_POST['last_name'], ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $role = htmlspecialchars($_POST['role'], ENT_QUOTES, 'UTF-8');
    $house_no = htmlspecialchars($_POST['house_no'], ENT_QUOTES, 'UTF-8');
    $street_name = htmlspecialchars($_POST['street_name'], ENT_QUOTES, 'UTF-8');
    $post_code = htmlspecialchars($_POST['post_code'], ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars($_POST['city'], ENT_QUOTES, 'UTF-8');
    $telephone = htmlspecialchars($_POST['telephone'], ENT_QUOTES, 'UTF-8');
    $emergency_contact = htmlspecialchars($_POST['emergency_contact'], ENT_QUOTES, 'UTF-8');
    $admin_id = $_SESSION['user_id'];

    // Assign a random profile picture based on role
    $role_folders = [
        'admin' => 'assets/admin/',
        'staff' => 'assets/staff/',
        'doctor' => 'assets/doctor/',
        'patient' => 'assets/patient/',
    ];

    $profile_picture = 'uploads/default.jpg'; // Default profile picture
    if (array_key_exists($role, $role_folders)) {
        $folder = $role_folders[$role];
        $images = glob($folder . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

        if ($images && count($images) > 0) {
            $profile_picture = $images[array_rand($images)]; // Assign random image
        }
    }

    // Insert into the database
    $stmt = $conn->prepare("
        INSERT INTO users 
        (first_name, last_name, username, password, email, role, house_no, street_name, post_code, city, telephone, emergency_contact, profile_picture) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sssssssssssss",
        $first_name,
        $last_name,
        $username,
        $password,
        $email,
        $role,
        $house_no,
        $street_name,
        $post_code,
        $city,
        $telephone,
        $emergency_contact,
        $profile_picture
    );

    if ($stmt->execute()) {
        // Log the action
        $logAction = "Admin ID: $admin_id \"{$_SESSION['username']}\" added a new user (Username: $username, Role: $role)";
        $logStmt = $conn->prepare("INSERT INTO logs (admin_id, action) VALUES (?, ?)");
        $logStmt->bind_param("is", $admin_id, $logAction);
        $logStmt->execute();
        $logStmt->close();

        $response['success'] = true;
        $response['message'] = 'User added successfully.';
    } else {
        $response['message'] = 'Error adding user: ' . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
