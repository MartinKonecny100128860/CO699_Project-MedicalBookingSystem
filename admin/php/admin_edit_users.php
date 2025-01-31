<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["action"]) && $_POST["action"] === "fetch" && isset($_POST["user_id"])) {
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

    if (isset($_POST["action"]) && $_POST["action"] === "update") {
        $user_id = intval($_POST["user_id"]);
        $username = $_POST["username"];
        $email = $_POST["email"];
        $role = $_POST["role"];
        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $house_no = $_POST["house_no"];
        $street_name = $_POST["street_name"];
        $post_code = $_POST["post_code"];
        $city = $_POST["city"];
        $telephone = $_POST["telephone"];
        $emergency_contact = $_POST["emergency_contact"];
        $gender = $_POST["gender"];
        $date_of_birth = !empty($_POST["date_of_birth"]) ? $_POST["date_of_birth"] : NULL;

        // ✅ Debugging - Output the received data
        error_log("Updating user ID: " . $user_id . " with DOB: " . $date_of_birth);

        // ✅ Ensure date_of_birth is handled correctly for NULL cases
        if ($date_of_birth === NULL) {
            $date_of_birth = NULL;
        }

        // Prepare update query
        $updateQuery = "UPDATE users SET username=?, email=?, role=?, first_name=?, last_name=?, house_no=?, street_name=?, post_code=?, city=?, telephone=?, emergency_contact=?, gender=?, date_of_birth=? WHERE user_id=?";
        $stmt = $conn->prepare($updateQuery);
        
        if (!$stmt) {
            echo json_encode(["error" => "Error preparing update statement: " . $conn->error]);
            exit();
        }

        // ✅ Corrected bind_param: 13 string parameters + 1 integer (user_id)
        $stmt->bind_param("sssssssssssssi", $username, $email, $role, $first_name, $last_name, $house_no, $street_name, $post_code, $city, $telephone, $emergency_contact, $gender, $date_of_birth, $user_id);

        if ($stmt->execute()) {
            // ✅ LOG UPDATE
            $logQuery = "INSERT INTO logs (admin_id, action, timestamp) VALUES (?, ?, NOW())";
            $logStmt = $conn->prepare($logQuery);
            $adminId = $_SESSION['user_id'];
            $logAction = "Updated user ID " . $user_id . " (DOB: " . $date_of_birth . ")";
            $logStmt->bind_param("is", $adminId, $logAction);
            $logStmt->execute();
            $logStmt->close();
        
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
?>
