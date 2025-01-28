<?php
session_start(); // Start a new session or resume the existing one for user session management.

// Database connection setup
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "MedicalBookingSystem";

// Create database connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Stop execution if the database connection fails.
}

$error = ''; // Variable to store error messages for invalid login attempts.

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; // Retrieve the username from the form.
    $password = $_POST['password']; // Retrieve the password from the form.

    // Prepare an SQL statement to securely retrieve user details, preventing SQL injection.
    $sql = "SELECT user_id, password, role, profile_picture FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error); // Handle errors in SQL preparation.
    }
    $stmt->bind_param("s", $username); // Bind the username parameter to the SQL query.
    $stmt->execute(); // Execute the SQL query.
    $stmt->store_result(); // Store the result for further processing.

    // Check if the user exists in the database.
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashed_password, $role, $profile_picture); // Bind the retrieved columns to variables.
        if ($stmt->fetch()) {
            // Handle legacy MD5-hashed passwords for backward compatibility.
            if (strlen($hashed_password) === 32 && md5($password) === $hashed_password) {
                // Update the old MD5 hash to a secure password hash.
                $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $updateStmt->bind_param("si", $new_hashed_password, $user_id);
                $updateStmt->execute();
                $updateStmt->close();

                // Update the hashed password variable for subsequent verification.
                $hashed_password = $new_hashed_password;
            }

            // Validate the provided password against the hashed password.
            if (password_verify($password, $hashed_password)) {
                $_SESSION['loggedin'] = true; // Set session variable to indicate the user is logged in.
                $_SESSION['user_id'] = $user_id; // Store the user's ID in the session.
                $_SESSION['role'] = $role; // Store the user's role in the session.

                // Set profile picture, or use a default image based on the user's role if not available.
                if (empty($profile_picture)) {
                    switch ($role) {
                        case 'admin':
                            $_SESSION['profile_picture'] = 'assets/defaults/admin_default.png';
                            break;
                        case 'doctor':
                            $_SESSION['profile_picture'] = 'assets/defaults/doctor_default.png';
                            break;
                        case 'staff':
                            $_SESSION['profile_picture'] = 'assets/defaults/staff_default.png';
                            break;
                        default:
                            $_SESSION['profile_picture'] = 'assets/defaults/user_default.png';
                            break;
                    }
                } else {
                    $_SESSION['profile_picture'] = $profile_picture;
                }

                // Redirect the user to the appropriate dashboard based on their role.
                switch ($role) {
                    case 'admin':
                        header("location: admindash.php");
                        break;
                    case 'doctor':
                        header("location: doctor_dashboard.php");
                        break;
                    case 'staff':
                        header("location: staff_dashboard.php");
                        break;
                    default:
                        $error = 'Invalid role'; // Handle unexpected roles.
                        break;
                }
                exit(); // Stop further execution after redirection.
            } else {
                $error = 'Invalid password'; // Set an error message for incorrect password.
            }
        }
    } else {
        $error = 'Invalid username'; // Set an error message for an invalid username.
    }
    $stmt->close(); // Close the prepared statement.
}

$conn->close(); // Close the database connection.
?>
             
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Page</title>
        <!-- Include Bootstrap for styling -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Include Google reCAPTCHA -->
        <script src="https://www.google.com/recaptcha/enterprise.js" async defer></script>
        <!-- Include Font Awesome for icons -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="adminstyles/highcontrast.css">
        <style>
            /* Body styling for centering content and adding gradient background */
            body { 
                display: flex; 
                flex-direction: column;
                height: 100vh; 
                align-items: center; 
                justify-content: center; 
                background: linear-gradient(135deg, #e3f2fd 0%, #e0f7fa 100%);
                font-family: 'Arial', sans-serif;
            }

            /* Logo styling for size and position */
            .logo {
                margin-bottom: 20px;
                width: 350px;
                height: auto;
                position: relative;
                left: 5px;
            }

            /* Login container with box shadow and hover effect */
            .login-container { 
                padding: 30px; 
                background: white; 
                border-radius: 10px; 
                box-shadow: 0 6px 20px rgba(0, 123, 255, 0.25);
                width: 100%;
                max-width: 360px;
                transition: transform 0.3s ease-in-out;
            }

            .login-container:hover {
                transform: scale(1.03);
            }

            /* Form group and label styling */
            .form-group {
                margin-bottom: 20px;
            }

            label {
                color: #06799e;
                font-weight: bold;
            }

            /* Input field styling with focus effect */
            .form-control {
                border: 1px solidrgb(122, 131, 191);
                border-radius: 5px;
                transition: border-color 0.3s ease-in-out;
            }

            .form-control:focus {
                border-color: #5d68b4;
            }

            /* Button styling for primary submit button */
            .btn-primary {
                background-color: #06799e;
                border-color: #06799e;
                border-radius: 5px;
                padding: 10px 24px;
                font-size: 16px;
                transition: background-color 0.3s;
            }

            .btn-primary:hover {
                background-color:rgb(2, 81, 107);
            }

            /* Centering the reCAPTCHA widget */
            .g-recaptcha {
                display: flex;
                justify-content: center;
            }

            /* Loading screen styling */
            #loading-screen {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: white;
                z-index: 2000;
                display: flex;
                justify-content: center;
                align-items: center;
                transition: opacity 0.5s ease-in-out;
            }

            #loading-screen img {
                width: 550px;
                height: 400px;
            }

            /* Styling for the page header */
            h2 {
                color: #06799e;
                text-align: center;
            }

            /* Password toggle button styling */
            #togglePassword {
                border: none;
                background: transparent;
                padding: 0 10px;
                height: 100%;
                display: flex;
                align-items: center;
                color:rgb(55, 56, 56);
                cursor: pointer;
            }

            #togglePassword:hover {
                color:rgb(17, 17, 18);
            }

            /* Add padding for input field with password toggle */
            #password {
                padding-right: 40px;
            }

            /* Input group for seamless design */
            .input-group {
                position: relative;
            }

            /* Styling for alert messages */
            .alert {
                margin-bottom: 20px;
            }

            /* Re-aligning CAPTCHA */
            .g-recaptcha {
                display: flex;
                justify-content: center;
            }
        </style>
    </head>
    <body>
        <!-- Include accessibility menu -->
        <?php include 'accessibility.php'; ?>

        <!-- Loading screen -->
        <div id="loading-screen">
            <img src="assets/loading2.gif" alt="Loading...">
        </div>

        <!-- Logo -->
        <img src="assets/logo-light.png" alt="Medical Booking System Logo" class="logo" id="logo">

        <!-- Login container -->
        <div class="login-container">
            <h2>Login to Your Account</h2>
            <!-- Display error messages if any -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <form id="login-form" action="" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group position-relative">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <span class="input-group-text position-absolute end-0" id="togglePassword">
                            <i id="passwordIcon" class="fa fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="g-recaptcha" data-sitekey="6LcMzLwqAAAAAClygQZytKsszDO9FvAGxkZdSoSo"></div>
                <br>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>

        <!-- Include chat feature -->
        <div id="chat-placeholder"></div>
        <script src="adminscripts/chat.js"></script>

        <!-- Include accessibility styles and scripts -->
        <link rel="stylesheet" href="adminstyles/accessibility.css">
        <script src="adminscripts/accessibility.js" defer></script>
    </body>
</html>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Hide the loading screen after 2.5 seconds
        const loadingScreen = document.getElementById("loading-screen");
        setTimeout(() => (loadingScreen.style.display = "none"), 2500);

        // Toggle password visibility when the toggle icon is clicked
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordInput = document.getElementById("password");
            const passwordIcon = document.getElementById("passwordIcon");
            const isText = passwordInput.type === "text"; // Check if the input type is 'text'
            passwordInput.type = isText ? "password" : "text"; // Toggle between 'password' and 'text'
            passwordIcon.classList.toggle("fa-eye", isText); // Update icon for visibility
            passwordIcon.classList.toggle("fa-eye-slash", !isText); // Update icon for invisibility
        });

        // Update the logo based on the current theme (light or dark)
        function updateLogoBasedOnTheme() {
            const logo = document.getElementById("logo");
            logo.src = document.body.classList.contains("dark-mode")
                ? "assets/logo-dark.png" // Use dark logo if dark mode is enabled
                : "assets/logo-light.png"; // Use light logo if dark mode is disabled
        }

        // Attach an event listener to the dark mode toggle button to update the logo
        document.getElementById("dark-mode-toggle").addEventListener("click", updateLogoBasedOnTheme);

        // Check localStorage for saved theme preference and apply it
        if (localStorage.getItem("darkMode") === "enabled") {
            document.body.classList.add("dark-mode"); // Enable dark mode
            updateLogoBasedOnTheme(); // Update logo for dark mode
        }
    });
</script>
