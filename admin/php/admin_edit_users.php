<?php
session_start();
header('Content-Type: application/json'); 

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["error" => "Unauthorized access."]);
    exit();
}

// Database connection setup
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "medicalbookingsystem";

// Create database connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
$conn->set_charset("utf8mb4");

// Check connection
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Handle Requests
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    // Fetch User Data
    if ($action === "fetch" && isset($_POST["user_id"])) {
        $user_id = intval($_POST["user_id"]);

        $stmt = $conn->prepare("SELECT user_id, username, email, role, first_name, last_name, house_no, street_name, post_code, city, telephone, emergency_contact, gender, profile_picture, 
            IFNULL(DATE_FORMAT(date_of_birth, '%Y-%m-%d'), '') AS date_of_birth FROM users WHERE user_id = ?");
        
        if (!$stmt) {
            echo json_encode(["error" => "Query preparation failed: " . $conn->error]);
            exit();
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            echo json_encode($user);
        } else {
            echo json_encode(["error" => "User not found."]);
        }

        $stmt->close();
        $conn->close();
        exit();
    }

    // Update User Data
    if ($action === "update") {
        $user_id = intval($_POST["user_id"]);
        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $role = trim($_POST["role"] ?? "");
        $first_name = trim($_POST["first_name"] ?? "");
        $last_name = trim($_POST["last_name"] ?? "");
        $house_no = trim($_POST["house_no"] ?? "");
        $street_name = trim($_POST["street_name"] ?? "");
        $post_code = trim($_POST["post_code"] ?? "");
        $city = trim($_POST["city"] ?? "");
        $telephone = trim($_POST["telephone"] ?? "");
        $emergency_contact = trim($_POST["emergency_contact"] ?? "");
        $gender = trim($_POST["gender"] ?? "Prefer not to say");
        $date_of_birth = !empty($_POST["date_of_birth"]) ? $_POST["date_of_birth"] : NULL;

        // Debugging
        error_log("Updating user ID: $user_id | DOB: " . ($date_of_birth ?? "NULL"));

        // Update Query
        $updateQuery = "UPDATE users 
                        SET username=?, email=?, role=?, first_name=?, last_name=?, house_no=?, street_name=?, post_code=?, city=?, telephone=?, emergency_contact=?, gender=?, date_of_birth=?
                        WHERE user_id=?";
        $stmt = $conn->prepare($updateQuery);

        if (!$stmt) {
            echo json_encode(["error" => "Error preparing update statement: " . $conn->error]);
            exit();
        }

        $stmt->bind_param("sssssssssssssi", $username, $email, $role, $first_name, $last_name, $house_no, $street_name, $post_code, $city, $telephone, $emergency_contact, $gender, $date_of_birth, $user_id);

        if ($stmt->execute()) {
            // **Check Log Table Timestamp Column Name**
            $logQuery = "INSERT INTO logs (admin_id, action, log_timestamp) VALUES (?, ?, NOW())"; // Ensure correct column name

            $logStmt = $conn->prepare($logQuery);
            
            if ($logStmt) {
                $adminId = $_SESSION['user_id'];
                $logAction = "Updated user ID $user_id (DOB: " . ($date_of_birth ?? "NULL") . ")";
                $logStmt->bind_param("is", $adminId, $logAction);
                $logStmt->execute();
                $logStmt->close();
            }

            echo json_encode(["message" => "User updated successfully."]);
        } else {
            echo json_encode(["error" => "Error updating user: " . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
        exit();
    }
}

echo json_encode(["error" => "Invalid request."]);
exit();
