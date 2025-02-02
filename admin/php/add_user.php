<?php
    session_start();

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        die("Unauthorized access.");
    }

    $conn = new mysqli("localhost", "root", "", "MedicalBookingSystem");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $email = $_POST['email'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $house_no = $_POST['house_no'] ?? '';
    $street_name = $_POST['street_name'] ?? '';
    $post_code = $_POST['post_code'] ?? '';
    $city = $_POST['city'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $emergency_contact = $_POST['emergency_contact'] ?? '';
    $gender = $_POST['gender'] ?? 'prefer not to say';
    $date_of_birth = !empty($_POST["date_of_birth"]) ? $_POST["date_of_birth"] : null;

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Profile picture handling
    $profile_picture = "assets/defaults/user_default.png";
    $upload_error = "";

    if (!empty($_FILES['profile_picture']['name'])) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_extensions)) {
            // Set upload directory based on user role
            $target_dir = "assets/$role/";

            // Ensure directory exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Use the original filename instead of renaming
            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);

            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $target_file;
            } else {
                $upload_error = "Error uploading profile picture.";
            }
        } else {
            $upload_error = "Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, email, first_name, last_name, house_no, street_name, post_code, city, telephone, emergency_contact, gender, profile_picture, date_of_birth) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssss", $username, $hashed_password, $role, $email, $first_name, $last_name, $house_no, $street_name, $post_code, $city, $telephone, $emergency_contact, $gender, $profile_picture, $date_of_birth);

    if ($stmt->execute()) {
        // Retrieve the newly created user ID
        $newUserId = $stmt->insert_id;

        // Log entry AFTER user is added
        $adminId = $_SESSION["user_id"];
        $logAction = "Added new user: $username (ID: $newUserId)";
        $logQuery = "INSERT INTO logs (admin_id, action, timestamp) VALUES (?, ?, NOW())";
        
        $logStmt = $conn->prepare($logQuery);
        if ($logStmt) {
            $logStmt->bind_param("is", $adminId, $logAction);
            $logStmt->execute();
            $logStmt->close();
        }

        echo "User added successfully.";
    } else {
        echo "Error adding user: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    if ($upload_error) {
        echo "<br>$upload_error";
    }
?>
