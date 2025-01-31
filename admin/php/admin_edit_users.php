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

        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
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

        // Initialize profile picture variable
        $profile_picture = null;

        if (!empty($_FILES["profile_picture"]["name"])) {
            $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
            $allowed_extensions = ["jpg", "jpeg", "png", "gif"];

            // If selected image is already in "assets/admin/", just save its path directly
            if (strpos($_FILES["profile_picture"]["name"], "assets/admin/") !== false) {
                $profile_picture = $_FILES["profile_picture"]["name"];
            }
            // Otherwise, process the uploaded file
            else if (in_array($file_extension, $allowed_extensions)) {
                $target_dir = "assets/$role/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $profile_picture = $target_dir . $username . "." . $file_extension;

                if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture)) {
                    echo json_encode(["error" => "File upload failed!"]);
                    exit();
                }
            } else {
                echo json_encode(["error" => "Invalid file format. Allowed: JPG, JPEG, PNG, GIF."]);
                exit();
            }
        }

        // Debugging - Output what is going to be saved in the database
        error_log("Profile picture path: " . ($profile_picture ? $profile_picture : "Not updated"));

        // Prepare update query
        if ($profile_picture) {
            $updateQuery = "UPDATE users SET username=?, email=?, role=?, first_name=?, last_name=?, house_no=?, street_name=?, post_code=?, city=?, telephone=?, emergency_contact=?, gender=?, profile_picture=? WHERE user_id=?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssssssssssssis", $username, $email, $role, $first_name, $last_name, $house_no, $street_name, $post_code, $city, $telephone, $emergency_contact, $gender, $profile_picture, $user_id);
        } else {
            $updateQuery = "UPDATE users SET username=?, email=?, role=?, first_name=?, last_name=?, house_no=?, street_name=?, post_code=?, city=?, telephone=?, emergency_contact=?, gender=? WHERE user_id=?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssssssssssssi", $username, $email, $role, $first_name, $last_name, $house_no, $street_name, $post_code, $city, $telephone, $emergency_contact, $gender, $user_id);
        }

        if ($stmt->execute()) {
            echo json_encode(["message" => "User updated successfully.", "profile_picture" => $profile_picture]);
        } else {
            error_log("Database update error: " . $stmt->error);
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
