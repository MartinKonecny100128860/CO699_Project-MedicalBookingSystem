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
        $date_of_birth = !empty($_POST["date_of_birth"]) ? $_POST["date_of_birth"] : NULL;

        // Step 1: Fetch original user data
        $originalStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $originalStmt->bind_param("i", $user_id);
        $originalStmt->execute();
        $originalResult = $originalStmt->get_result();
        $original = $originalResult->fetch_assoc();
        $originalStmt->close();

        if (!$original) {
            echo json_encode(["error" => "Original user not found."]);
            exit();
        }

        // ✅ Now safe to handle gender
        $gender = isset($_POST["gender"]) ? trim($_POST["gender"]) : $original['gender'];

        // ✅ Handle new password logic
        $new_password = isset($_POST["new_password"]) ? trim($_POST["new_password"]) : null;
        $hashed_password = $original['password'];
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // Step 2: Update query
        $updateQuery = "UPDATE users 
            SET username=?, email=?, role=?, first_name=?, last_name=?, house_no=?, street_name=?, post_code=?, city=?, telephone=?, emergency_contact=?, gender=?, date_of_birth=?, password=?
            WHERE user_id=?";
        $stmt = $conn->prepare($updateQuery);

        if (!$stmt) {
            echo json_encode(["error" => "Error preparing update statement: " . $conn->error]);
            exit();
        }

        $stmt->bind_param(
            "ssssssssssssssi", 
            $username, $email, $role, $first_name, $last_name, 
            $house_no, $street_name, $post_code, $city, $telephone, 
            $emergency_contact, $gender, $date_of_birth, $hashed_password, $user_id
        );

        if ($stmt->execute()) {
            // Step 3: Log what changed
            $changes = [];
            if ($username !== $original['username']) $changes[] = "username";
            if ($email !== $original['email']) $changes[] = "email";
            if ($role !== $original['role']) $changes[] = "role";
            if ($first_name !== $original['first_name']) $changes[] = "first name";
            if ($last_name !== $original['last_name']) $changes[] = "last name";
            if ($house_no !== $original['house_no']) $changes[] = "house number";
            if ($street_name !== $original['street_name']) $changes[] = "street name";
            if ($post_code !== $original['post_code']) $changes[] = "post code";
            if ($city !== $original['city']) $changes[] = "city";
            if ($telephone !== $original['telephone']) $changes[] = "telephone";
            if ($emergency_contact !== $original['emergency_contact']) $changes[] = "emergency contact";
            if ($gender !== $original['gender']) $changes[] = "gender";
            if ($date_of_birth !== $original['date_of_birth']) $changes[] = "DOB";
            if (!empty($new_password)) $changes[] = "password";

            if (!empty($changes)) {
                $logAction = "Updated user ID $user_id (Changed: " . implode(", ", $changes) . ")";
                $logQuery = "INSERT INTO logs (admin_id, action, log_timestamp) VALUES (?, ?, NOW())";
                $logStmt = $conn->prepare($logQuery);
                if ($logStmt) {
                    $adminId = $_SESSION['user_id'];
                    $logStmt->bind_param("is", $adminId, $logAction);
                    $logStmt->execute();
                    $logStmt->close();
                }
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
